const core = require('@actions/core'); // @TODO move to 'imports from' when moved to TS !

const SDK = require('./node-sdk');  // @TODO move to 'imports from' when moved to TS !

// @TODO replace json by glob-string as output ?? Same as string format but with glob compatible path list
// (easier to manage for inner code, while end-user is still able to fall back on string format with a simple split)
async function run() {
    const trustedPathConverter = SDK.path.trustedPathHelpers();
    /** INPUTS **/
    const PATH_INPUT = core.getInput('path', {required: true});
    // Following inputs are not marked as required by the action but a default value must be there, so using `required` works
    const FORMAT_INPUT = core.getInput('format', {required: true});
    const GLUE_STRING_INPUT = core.getInput('glue-string', {required: true, trimWhitespace: false});
    const FOLLOW_SYMLINK_INPUT = core.getBooleanInput('follow-symbolic-links', {required: true});

    let trustedMetadataList = await core.group(
        'Build metadata list',
        async () => SDK.load.loadMetadataListFrom(PATH_INPUT, trustedPathConverter, {followSymbolicLinks: FOLLOW_SYMLINK_INPUT})
    );
    core.debug('Metadata list=' + JSON.stringify(trustedMetadataList));
    if (0 === trustedMetadataList.length) {
        core.setFailed('Unable to retrieve any metadata. Something wrong most likely happened !');
    }

    core.info('Build action outputs');
    const isGlobString = 'glob-string' === FORMAT_INPUT;

    SDK.outputs.bindFrom({
        metadata: JSON.stringify({
            name: SDK.array.mergeStringList(SDK.array.itemsPropertyList(trustedMetadataList, 'name')).join(GLUE_STRING_INPUT),
            format: SDK.array.mergeStringList(SDK.array.itemsPropertyList(trustedMetadataList, 'format')).join(GLUE_STRING_INPUT),
            reports: SDK.array.mergeListOfList(SDK.array.itemsPropertyList(trustedMetadataList, 'reports')).join(isGlobString ? '\n' : GLUE_STRING_INPUT),
            flags: SDK.array.mergeListOfList(SDK.array.itemsPropertyList(trustedMetadataList, 'flags')).join(GLUE_STRING_INPUT),
            path: SDK.array.mergeStringList(SDK.array.itemsPropertyList(trustedMetadataList, 'path')).join(isGlobString ? '\n' : GLUE_STRING_INPUT),
        }),
    });
}

run();
