const core = require("@actions/core");
const {find: findSdk} = require("node-sdk");

async function run() {
    /** INPUTS **/
    const PATH_INPUT = core.getInput('path', {required: true});
    // Following inputs are not marked as required by the action but a default value must be there, so using `required` works
    const FORMAT_INPUT = core.getInput('format', {required: true});
    const GLUE_STRING_INPUT = core.getInput('glue-string', {required: true, trimWhitespace: false});
    const FOLLOW_SYMLINK_INPUT = core.getBooleanInput('follow-symbolic-links', {required: true});

    /** Resolve paths **/
    const groupDirPathList = findSdk.groupPaths(PATH_INPUT, {followSymbolicLinks: FOLLOW_SYMLINK_INPUT});
    if (0 === groupDirPathList.length) {
        core.setFailed('Unable to retrieve any group. Something wrong most likely happened !');
    }

    /** Build action output **/
    core.setOutput(
        'list',
        'json' === FORMAT_INPUT
            ? JSON.stringify(groupDirPathList)
            : groupDirPathList.join(GLUE_STRING_INPUT)
    );
}

run();
