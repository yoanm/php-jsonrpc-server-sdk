const {statSync} = require('fs'); // @TODO move to 'imports from' when moved to TS !
const path = require("path"); // @TODO move to 'imports from' when moved to TS !

const core = require('@actions/core'); // @TODO move to 'imports from' when moved to TS !

const SDK = require('./node-sdk'); // @TODO move to 'imports from' when moved to TS !

// @TODO replace json by glob-string as output ?? Same as string format but with glob compatible path list

// (easier to manage for inner code, while end-user is still able to fall back on string format with a simple split)
async function run() {
    const trustedPathConverter = SDK.path.trustedPathHelpers();
    /** INPUTS **/
    const PATH_INPUT = core.getInput('path', {required: true});
    // Following inputs are not marked as required by the action but a default value must be there, so using `required` works
    const FORMAT_INPUT = core.getInput('format', {required: true});
    const GLUE_STRING_INPUT = core.getInput('format', {required: 'json' !== FORMAT_INPUT, trimWhitespace: false});
    const FOLLOW_SYMLINK_INPUT = core.getBooleanInput('follow-symbolic-links', {required: true});
    const ENABLE_ARTIFACT_MODE = core.getBooleanInput('artifacts-mode', {required: true});
    const ENABLE_MATRIX_MODE = core.getBooleanInput('matrix-mode', {required: true});
    const ENABLE_PATH_ONLY_MODE = core.getBooleanInput('matrix-mode', {required: true});
    // Following inputs are optionals !
    const GROUP_BY_INPUT = core.getInput('group-by');
    const trustedPathInput = trustedPathConverter.trust(PATH_INPUT);

    let trustedMetadataList = await core.group(
        'Build metadata list',
        async () => SDK.load.loadMetadataListFrom(trustedPathInput, trustedPathConverter, {followSymbolicLinks: FOLLOW_SYMLINK_INPUT})
    );
    core.debug('Metadata list=' + JSON.stringify(trustedMetadataList));
    if (0 === trustedMetadataList.length) {
        core.setFailed('Unable to retrieve any metadata. Something wrong most likely happened !');
    }

    // "Filtering" phase
    core.debug('Filter metadata list');
    trustedMetadataList = SDK.filter.filterMetadataList(
        trustedMetadataList,
        core.getMultilineInput('exclude-groups'), core.getMultilineInput('exclude-formats'), core.getMultilineInput('exclude-flags'), core.getMultilineInput('exclude-paths'),
        core.getMultilineInput('only-include-groups'), core.getMultilineInput('only-include-formats'), core.getMultilineInput('only-include-flags'), core.getMultilineInput('only-include-paths')
    );
    core.debug('New metadata list=' + JSON.stringify(trustedMetadataList));
    // Apply `artifacts` mode if needed
    if (ENABLE_ARTIFACT_MODE) {
        core.debug('Apply artifact mode');
        if (trustedPathInput.split('\n').length !== 1 || trustedPathInput.includes('*') || trustedPathInput.includes('?') || trustedPathInput.includes(']')) {
            core.setFailed('You must provide a single valid path when `artifacts-mode` option is enabled !')
        }
        if (!statSync(trustedPathInput).isDirectory()) {
            core.setFailed('You must provide a single valid path when `artifacts-mode` option is enabled !')
        }
        const artifactDownloadDirectory = SDK.path.withTrailingSeparator(path.resolve(trustedPathInput));
        const artifactList = new Set();

        trustedMetadataList = trustedMetadataList.map(metadata => {
            const absGroupPath = path.resolve(metadata.path);
            const pathFromDwlDirectory = path.normalize(absGroupPath.replace(artifactDownloadDirectory, ''));
            const [artifactName] = pathFromDwlDirectory.split(path.sep);

            metadata.artifact = artifactName;
            artifactList.add(metadata.artifact);
            metadata.path = pathFromDwlDirectory;
            metadata.path = SDK.path.withTrailingSeparator(0 === metadata.path.length ? '.' : metadata.path);
            metadata.reports = metadata.reports.map(v => path.relative(v, artifactDownloadDirectory));

            return metadata;
        });
        core.debug('New metadata list=' + JSON.stringify(trustedMetadataList));
        SDK.outputs.bindFrom({artifacts: JSON.stringify([...artifactList].join('\n'))});
        core.debug('Artifact mode applied');
    }
    // Apply `path-only` mode ?
    if (ENABLE_PATH_ONLY_MODE) {
        core.debug('Apply paths-only mode');
        SDK.outputs.bindFrom({
            count: trustedMetadataList.length,
            paths: trustedMetadataList.map(({reports}) => reports).flat().join('\n'),
        });
        core.debug('paths-only mode applied, stopping there');

        return;
    }
    // "Grouping" phase
    /** @type {Metadata[][]} */
    let trustedMetadataListOfList = [trustedMetadataList];
    /** @type {MergeField[]} */
    const groupByItemList = GROUP_BY_INPUT.length > 0 ? GROUP_BY_INPUT.split(',') : [];
    const isMultiGroup = groupByItemList.length > 0;
    if (isMultiGroup) {
        core.info('Merge metadata list');
        trustedMetadataListOfList = SDK.merge.groupMetadataList(trustedMetadataList, groupByItemList);
        core.debug('New metadata list=' + JSON.stringify(trustedMetadataListOfList));
    }
    // "Merging" phase
    core.info('Build action outputs');
    const outputs = SDK.format.convertMetadataListToFindActionOutput(trustedMetadataListOfList, FORMAT_INPUT, GLUE_STRING_INPUT, ENABLE_ARTIFACT_MODE)

    // Apply `matrix-mode`
    if (ENABLE_MATRIX_MODE) {
        core.debug('Apply matrix mode');
        SDK.outputs.bindFrom({
            ...outputs,
            list: undefined,
            matrix: JSON.stringify({
                includes: outputs.list.map(md => Array.isArray(md.name) ? JSON.stringify(md) : md)
            }),
        });
        core.debug('Matrix mode applied');
    } else {
        SDK.outputs.bindFrom({
            ...outputs,
            list: JSON.stringify(outputs.list)
        });
    }
}

run();
