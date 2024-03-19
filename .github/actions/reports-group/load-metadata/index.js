import core from "@actions/core";
import {find as findSDK, load as loadSDK} from "node-sdk";
import * as path from "path";

async function run() {
    /** INPUTS **/
    const PATH_INPUT = core.getInput('path', {required: true});
    // Following inputs are not marked as required by the action but a default value must be there, so using `required` works
    const FORMAT_INPUT = core.getInput('format', {required: true});
    const GLUE_STRING_INPUT = core.getInput('glue-string', {required: true, trimWhitespace: false});
    const FOLLOW_SYMLINK_INPUT = core.getBooleanInput('follow-symbolic-links', {required: true});

    /** build-metadata **/
    const metadataPathList = findSDK.metadataPaths(PATH_INPUT, {followSymbolicLinks: FOLLOW_SYMLINK_INPUT});
    if (0 === metadataPathList.length) {
        core.setFailed('Unable to retrieve any group. Something wrong most likely happened !');
    }
    const metadataList = metadataPathList.map(p => loadSDK.metadataFile(p));

    /** Build action output **/
    if ('json' === FORMAT_INPUT) {
        // Detect if provided `paths` was a group directory
        const isSingleMetadata = metadataList.length === 1 && path.resolve(metadataList[0].path) === path.resolve(PATH_INPUT);
        const result = isSingleMetadata ? metadataList.shift() : metadataList;
        core.setOutput('metadata', JSON.stringify(result));
    } else {
        const formatScalar = (key) => [...(new Set(metadataList.map(m => m[key]))).values()].join(GLUE_STRING_INPUT);
        const formatList = (key) => [...(new Set(metadataList.map(m => m[key]).flat())).values()].join(GLUE_STRING_INPUT);
        const result = {
            name: formatScalar('name'),
            format: formatScalar('format'),
            reports: formatList('reports'),
            flags: formatList('flags'),
            path: formatScalar('path'),
            reportPaths: formatList('reportPaths')
        };
        core.setOutput('metadata', JSON.stringify(result));
    }
}

run();
