const path = require('path'); // @TODO move to 'imports from' when moved to TS !

const core = require('@actions/core'); // @TODO move to 'imports from' when moved to TS !

const SDK = require('./node-sdk');  // @TODO move to 'imports from' when moved to TS !

async function run() {
    const trustedPathConverter = SDK.path.trustedPathHelpers();
    /** INPUTS **/
    const PATH_INPUT = core.getInput('path', {required: true});
    // Following inputs are not marked as required by the action but a default value must be there, so using `required` works
    const FORMAT_INPUT = core.getInput('format', {required: true});
    const GLUE_STRING_INPUT = core.getInput('glue-string', {required: true, trimWhitespace: false});
    const FOLLOW_SYMLINK_INPUT = core.getBooleanInput('follow-symbolic-links', {required: true});

    const trustedMetadataList = await core.group(
        'Build metadata list',
        async () => {
            const trustedMetadataPathList = await SDK.find.trustedGroupPaths(PATH_INPUT, trustedPathConverter.toWorkspaceRelative, {followSymbolicLinks: FOLLOW_SYMLINK_INPUT});
            if (0 === trustedMetadataPathList.length) {
                core.setFailed('Unable to retrieve any group. Something wrong most likely happened !');
            }

            return trustedMetadataPathList.map((trustedGroupPath) => {
                core.info('Load '+ trustedGroupPath);

                return trustedPathConverter.trustedMetadataUnder(trustedGroupPath);
            });
        }
    );
    core.debug('Group paths=' + JSON.stringify(trustedMetadataList));

    const outputs = await core.group(
        'Build action outputs',
        async () => {
            const res = {};

            // @TODO move back to dedicated properties (merge array/object properties one by one in case of multi result with json output)
            core.info("Build 'metadata' output");
            if ('json' === FORMAT_INPUT) {
                // Detect if provided `paths` was an actual group directory
                const isSingleMetadata = trustedMetadataList.length === 1 && path.resolve(trustedMetadataList[0].path) === path.resolve(PATH_INPUT);
                res.metadata = isSingleMetadata ? trustedMetadataList.shift() : trustedMetadataList;
            } else {
                const formatScalar = (key) => [...(new Set(trustedMetadataList.map(m => m[key]))).values()].join(GLUE_STRING_INPUT);
                const formatList = (key) => [...(new Set(trustedMetadataList.map(m => m[key]).flat())).values()].join(GLUE_STRING_INPUT);

                res.metadata = {
                    name: formatScalar('name'),
                    format: formatScalar('format'),
                    reports: formatList('reports'),
                    flags: formatList('flags'),
                    path: formatScalar('path'),
                    reportPaths: formatList('reportPaths')
                };
            }

            return res;
        }
    );
    core.debug('outputs=' + JSON.stringify(outputs));
    SDK.outputs.bindFrom(outputs);
}

run();
