import core from "@actions/core";
import io from "@actions/io";
import * as path from "path";
import * as fs from "fs";

import {path as pathSDK, glob as globSDK, CONSTANTS as SDK_CONSTANTS} from "node-sdk";

async function run() {
    /** INPUTS **/
    const NAME_INPUT = core.getInput('NAME', {required: true});
    const FORMAT_INPUT = core.getInput('format', {required: true});
    const REPORTS_INPUT = core.getInput('files', {required: true});
    // Following inputs are not marked as required by the action but a default value must be there, so using `required` works
    const PATH_INPUT = core.getInput('path', {required: true});
    const FLAG_LIST_INPUT = core.getMultilineInput('flags', {required: true});
    const FOLLOW_SYMLINK_INPUT = core.getBooleanInput('follow-symbolic-links', {required: true});

    /** resolve-directory **/
    const groupDirectory = await core.group('Resolve group directory path', async () => path.resolve(PATH_INPUT, NAME_INPUT));
    core.debug('group directory=' + groupDirectory);

    /** resolve-files **/
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

    /** build-reports-map */
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

    /** build-metadata */
    const metadata = await core.group(
        'Build group metadata',
        async () => ({name: NAME_INPUT, format: FORMAT_INPUT, reports: reportsMap.map(v => v.filename), flags: FLAG_LIST_INPUT})
    );
    core.debug('metadata=' + JSON.stringify(metadata));

    await core.group('Create group directory', () => io.mkdirP(groupDirectory));

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
            fs.writeFileSync(filepath, JSON.stringify(metadata));
    });

    /** build-outputs */
    await core.group('Build outputs', () => {
        core.setOutput('path', groupDirectory);
        core.setOutput('reports', metadata.reports.join('\n'));
        core.setOutput('files', originalReportPaths.join('\n'));
    })
}

run();
