const core = require('@actions/core');
const glob = require('@actions/glob');

const path = require('path');

export async function run() {
    try {
        const PATH_LIST_INPUT = core.getMultilineInput('path', {required: true});
        // Following inputs are not marked as required by the action but a default value must be there, so using `required` works
        const FORMAT_INPUT = core.getInput('format', {required: true});
        const GLUE_STRING_INPUT = core.getInput('glue-string', {required: true, trimWhitespace: false});
        const FOLLOW_SYMLINK_INPUT = core.getBooleanInput('follow-symbolic-links', {required: true});

        const absWorkspace = path.resolve('.');

        //resolve-paths
        const pattern = PATH_LIST_INPUT.map(item => path.join(item, '**', '.reports-group-metadata.json')).join('\n');
        core.debug('glob pattern: ' + pattern);
        const globber = await glob.create(pattern, {followSymbolicLinks: FOLLOW_SYMLINK_INPUT});
        const groupDirPathList = [];
        for await (const fp of globber.globGenerator()) {
            // Clean path by removing the file name and the workspace directory
            const cleanPath = path.dirname(fp).replace(absWorkspace + path.sep, '')
            core.info('Found a reports group directory at ' + cleanPath);
            groupDirPathList.push(cleanPath);
        }
        if (0 === groupDirPathList.length) {
            core.setFailed('Unable to retrieve any group. Something wrong most likely happened !');
        }

        // Build action output
        core.setOutput(
            'list',
            'json' === FORMAT_INPUT
                ? JSON.stringify(groupDirPathList)
                : groupDirPathList.join(GLUE_STRING_INPUT)
        );

    } catch (error) {
        core.setFailed(error.message);
    }
}
