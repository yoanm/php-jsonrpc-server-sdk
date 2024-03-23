const core = require('@actions/core'); // @TODO move to 'imports from' when moved to TS !

const SDK = require('./node-sdk'); // @TODO move to 'imports from' when moved to TS !

// @TODO replace json by glob-string as output ?? Same as string format but with glob compatible path list
// (easier to manage for inner code, while end-user is still able to fallback on string format with a simple split)
async function run() {
    const trustedPathConverter = SDK.path.trustedPathHelpers();
    /** INPUTS **/
    const PATH_INPUT = core.getInput('path', {required: true});
    // Following inputs are not marked as required by the action but a default value must be there, so using `required` works
    const FORMAT_INPUT = core.getInput('format', {required: true});
    const GLUE_STRING_INPUT = core.getInput('glue-string', {required: true, trimWhitespace: false});
    const FOLLOW_SYMLINK_INPUT = core.getBooleanInput('follow-symbolic-links', {required: true});

    const trustedGroupPaths = await core.group(
        'Find groups',
        async () => {
            const res = (await SDK.find.trustedGroupPaths(PATH_INPUT, trustedPathConverter.toWorkspaceRelative, {followSymbolicLinks: FOLLOW_SYMLINK_INPUT}));

            res.forEach(p => core.info('Found a reports group directory at ' + p));

            return res;
        }
    );
    core.debug('Group paths=' + JSON.stringify(trustedGroupPaths));
    if (0 === trustedGroupPaths.length) {
        core.setFailed('Unable to retrieve any group. Something wrong most likely happened !');
    }

    const outputs = await core.group(
        'Build action outputs',
        async () => {
            const res = {};

            core.info("Build 'list' output");
            const list = trustedGroupPaths.map(v => SDK.path.withTrailingSeparator(v));
            res.list = 'json' === FORMAT_INPUT ? JSON.stringify(list)  : list.join(GLUE_STRING_INPUT)

            return res;
        }
    );
    core.debug('outputs=' + JSON.stringify(outputs));
    SDK.outputs.bindFrom(outputs);
}

run();
