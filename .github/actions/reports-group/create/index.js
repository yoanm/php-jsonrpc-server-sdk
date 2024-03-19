const path = require('path'); // @TODO move to 'imports from' when moved to TS !
const fs = require('fs'); // @TODO move to 'imports from' when moved to TS !

const core = require('@actions/core'); // @TODO move to 'imports from' when moved to TS !
const io = require('@actions/io'); // @TODO move to 'imports from' when moved to TS !

const {path: pathSDK, glob: globSDK, outputs: outputsSDK, CONSTANTS: SDK_CONSTANTS} = require('./node-sdk'); // @TODO move to 'imports from' when moved to TS !

async function run() {
    /** INPUTS **/
    const NAME_INPUT = core.getInput('NAME', {required: true});
    const FORMAT_INPUT = core.getInput('format', {required: true});
    const REPORTS_INPUT = core.getInput('files', {required: true});
    // Following inputs are not marked as required by the action but a default value must be there, so using `required` works
    const PATH_INPUT = core.getInput('path', {required: true});
    const FLAG_LIST_INPUT = core.getMultilineInput('flags', {required: true});
    const FOLLOW_SYMLINK_INPUT = core.getBooleanInput('follow-symbolic-links', {required: true});

    const groupDirectory = await core.group(
        'Resolve group directory path',
        async () => {
            const res = path.resolve(PATH_INPUT, NAME_INPUT);
            core.info('group directory=' + res);

            return res;
        }
    );

    const originalReportPaths = await core.group(
        'Resolve reports',
        async () => {
            const result = [];
            for await (const fp of globSDK.lookup(REPORTS_INPUT, {followSymbolicLinks: FOLLOW_SYMLINK_INPUT})) {
                const normalizedFp = pathSDK.relativeToGHWorkspace(fp);
                core.info('Found ' + normalizedFp);
                result.push(normalizedFp);
            }
            return result;
        }
    );
    core.debug('reports to copy=' + JSON.stringify(originalReportPaths));

    if (0 === originalReportPaths.length) {
        core.setFailed('You must provide at least one report !');
    }

    const reportsMap = await core.group(
        'Build reports map',
        async () => {
            let counter = 0;
            return originalReportPaths.map(filepath => {
                // Ensure report files uniqueness while keeping a bit of clarity regarding the mapping with original files !
                const filename = path.basename(filepath) + '-report-' + (++counter);
                const destination = pathSDK.relativeToGHWorkspace(groupDirectory, filename);
                core.info(filepath + ' => ' + destination);
                return {source: filepath, filename: filename, dest: destination};
            });
        }
    );
    core.debug('reports map=' + JSON.stringify(reportsMap));

    const metadata = await core.group(
        'Build group metadata',
        async () => {
            const res = {
                name: NAME_INPUT,
                format: FORMAT_INPUT,
                reports: reportsMap.map(v => v.filename),
                flags: FLAG_LIST_INPUT
            };
            core.info('Created');

            return res;
        }
    );
    core.debug('metadata=' + JSON.stringify(metadata));

    await core.group('Create group directory', () => {
        core.info('Create group directory at ' + groupDirectory);

        return io.mkdirP(groupDirectory)
    });

    await core.group(
        'Copy reports',
        async () => reportsMap.map(async ({source, dest}) => {
            core.info(source + ' => ' + dest);

            return io.cp(source, dest);
        })
    );

    await core.group(
        'Create metadata file',
        async () => {
            const filepath = path.join(groupDirectory, SDK_CONSTANTS.METADATA_FILENAME);
            core.info('Create metadata file at ' + filepath + ' with: ' + JSON.stringify(metadata));
            fs.writeFileSync(filepath, JSON.stringify(metadata));
    });

    const outputs = await core.group(
        'Build action outputs',
        async () => {
            const res = {};

            core.info("Build 'path' output");
            res.path = groupDirectory;
            core.info("Build 'reports' output");
            res.reports = metadata.reports.join('\n');
            core.info("Build 'files' output");
            res.files = originalReportPaths.join('\n');

            return res;
        }
    );
    core.debug('outputs=' + JSON.stringify(outputs));
    outputsSDK.bindActionOutputs(outputs);
}

run();
