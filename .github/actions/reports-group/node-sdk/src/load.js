const core = require('@actions/core'); // @TODO move to 'imports from' when moved to TS !

const path = require("path");

const find = require("./find");

/**
 * @param {string} globPattern
 * @param trustedPathConverter
 * @param {import('@actions/glob').GlobOptions|undefined} globOptions
 *
 * @return {import('./interfaces').Metadata[]}
 */
export async function loadMetadataListFrom(globPattern, trustedPathConverter, globOptions) {
    const trustedMetadataPathList = await find.trustedMetadataPaths(globPattern, trustedPathConverter.toWorkspaceRelative, globOptions);

    return trustedMetadataPathList.map((trustedMetadataPath) => {
        const trustedGroupPath = path.dirname(trustedMetadataPath);
        core.info('Load ' + trustedGroupPath);

        return trustedPathConverter.trustedMetadataUnder(trustedGroupPath);
    });
}
