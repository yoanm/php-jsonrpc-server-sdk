const path = require('path'); // @TODO move to 'imports from' when moved to TS !

const core = require('@actions/core'); // @TODO move to 'imports from' when moved to TS !

const {METADATA_FILENAME} = require('./constants');
const glob = require('./glob');

/**
 * @param {string} globPattern
 * @param {function(untrusted: string): string} toTrustedPath A function ensuring path is valid before returning it
 * @param {import('@actions/glob').GlobOptions|undefined} globOptions
 *
 * @returns {Promise<string[]>} Trusted metadata path list
 */
export async function trustedMetadataPaths(globPattern, toTrustedPath, globOptions = undefined) {
    const finalPattern = globPattern.split('\n').map(item => toTrustedPath(path.join(item.trim(), '**', METADATA_FILENAME))).join('\n');
    core.debug('Find metadata paths with ' + globPattern);

    const list = [];
    for await (const fp of glob.lookup(finalPattern, {...globOptions, implicitDescendants: false})) {
        list.push(fp);
    }

    return list;
}
