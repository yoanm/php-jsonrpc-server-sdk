const core = require('@actions/core'); // @TODO move to 'imports from' when moved to TS !

const {find: findSdk, outputs: outputsSDK} = require('./node-sdk'); // @TODO move to 'imports from' when moved to TS !

async function run() {
    /** INPUTS **/
    const PATH_INPUT = core.getInput('path', {required: true});
    // Following inputs are not marked as required by the action but a default value must be there, so using `required` works
    const FORMAT_INPUT = core.getInput('format', {required: true});
    const GLUE_STRING_INPUT = core.getInput('glue-string', {required: true, trimWhitespace: false});
    const FOLLOW_SYMLINK_INPUT = core.getBooleanInput('follow-symbolic-links', {required: true});

    const groupDirPathList = await core.group(
        'Find groups',
        async () => {
            const groupDirPathList = await findSdk.groupPaths(PATH_INPUT, {followSymbolicLinks: FOLLOW_SYMLINK_INPUT});

            groupDirPathList.forEach(p => core.info('Found a reports group directory at ' + p));

            return groupDirPathList;
        }
    );
    core.debug('groupDirPathList=' + JSON.stringify(groupDirPathList));
    if (0 === groupDirPathList.length) {
        core.setFailed('Unable to retrieve any group. Something wrong most likely happened !');
    }

    const outputs = await core.group(
        'Build action outputs',
        async () => {
            const res = {};

            core.info("Build 'list' output");
            res.list = 'json' === FORMAT_INPUT ? JSON.stringify(groupDirPathList)  : groupDirPathList.join(GLUE_STRING_INPUT)

            return res;
        }
    );
    core.debug('outputs=' + JSON.stringify(outputs));
    outputsSDK.bindActionOutputs(outputs);
}

run();
