const path = require('path'); // @TODO move to 'imports from' when moved to TS !

const core = require('@actions/core'); // @TODO move to 'imports from' when moved to TS !

const {find: findSDK, load: loadSDK, outputs: outputsSDK} = require('./node-sdk');

async function run() {
    /** INPUTS **/
    const PATH_INPUT = core.getInput('path', {required: true});
    // Following inputs are not marked as required by the action but a default value must be there, so using `required` works
    const FORMAT_INPUT = core.getInput('format', {required: true});
    const GLUE_STRING_INPUT = core.getInput('glue-string', {required: true, trimWhitespace: false});
    const FOLLOW_SYMLINK_INPUT = core.getBooleanInput('follow-symbolic-links', {required: true});

    const metadataList = await core.group(
        'Build metadata list',
        async () => {
            const metadataPathList = await findSDK.metadataPaths(PATH_INPUT, {followSymbolicLinks: FOLLOW_SYMLINK_INPUT});
            if (0 === metadataPathList.length) {
                core.setFailed('Unable to retrieve any group. Something wrong most likely happened !');
            }

            return Promise.all(
                metadataPathList.map(async (fp) => {
                    core.info('Load '+ fp);

                    return loadSDK.metadataFile(fp);
                })
            );
        }
    );
    core.debug('metadataList=' + JSON.stringify(metadataList));

    const outputs = await core.group(
        'Build action outputs',
        async () => {
            const res = {};

            core.info("Build 'metadata' output");
            if ('json' === FORMAT_INPUT) {
                // Detect if provided `paths` was a group directory
                const isSingleMetadata = metadataList.length === 1 && path.resolve(metadataList[0].path) === path.resolve(PATH_INPUT);
                res.metadata = isSingleMetadata ? metadataList.shift() : metadataList;
            } else {
                const formatScalar = (key) => [...(new Set(metadataList.map(m => m[key]))).values()].join(GLUE_STRING_INPUT);
                const formatList = (key) => [...(new Set(metadataList.map(m => m[key]).flat())).values()].join(GLUE_STRING_INPUT);

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
    outputsSDK.bindActionOutputs(outputs);
}

run();
