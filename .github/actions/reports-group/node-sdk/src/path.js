const fs = require('fs');
const core = require('@actions/core');

const path = require('path'); // @TODO move to 'imports from' when moved to TS !

const {GITHUB_WORKSPACE} = process.env;
const {METADATA_FILENAME} = require('./constants');

/**
 * Ensure a trailing separator exists. Easier to re-use for end-user
 *
 * @param {string} untrustedPath
 *
 * @returns {string} same *untrusted* path with a trailing separator
 */
export function withTrailingSeparator(untrustedPath) {
    //  by adding an additional trailing separator
    // which will be removed by `path.normalize()` in case it is useless
    return path.normalize(untrustedPath + path.sep);
}

export function trustedPathHelpers() {
    return trustFrom(GITHUB_WORKSPACE);
}

function avoidPoisonNullBytesAttack(untrustedPath) {
    if (untrustedPath.indexOf('\0') !== -1) {
        throw new Error('Potential "Poison Null Bytes" attack detected !');
    }
}
function avoidRelativePathAttack(trustedRootPath, untrustedPath) {
    const normalizedPath = path.resolve(untrustedPath);
    if (normalizedPath.indexOf(trustedRootPath) !== 0) {
        throw new Error(
            'Potential "Relative Path" attack detected !\n'
            + ' Trusted root: "' + trustedRootPath + '"'
            + ' Path: "' + normalizedPath + '"'
            + (untrustedPath !== normalizedPath ? ' (provided: "' + untrustedPath + '")' : '')
        );
    }
}

function trustFrom(workspacePath) {
    // Ensure workspace path is ok
    avoidPoisonNullBytesAttack(workspacePath)
    if (!path.isAbsolute(workspacePath)) {
        throw new Error('Workspace path must be an absolute path');
    }
    const helpers = {
        trust: (untrustedPath) => {
            avoidPoisonNullBytesAttack(untrustedPath);
            avoidRelativePathAttack(workspacePath, untrustedPath);

            return untrustedPath; // Becomes trusted then :)
        },
        /**
         * @TODO remove that function and always work and return with absolute path ! (first, either update codecov-upload-from-artifacts.yml code or create a find-from-artifacts action to keep it small)
         * @param {string} untrustedPath
         *
         * @returns {string} Trusted relative path from workspace directory to `untrustedPath`
         */
        toWorkspaceRelative: (untrustedPath) => {
            return helpers.trust(path.relative(workspacePath, untrustedPath));
        },
        /**
         * @param {string} untrustedGroupPath
         * @returns {{name: string, format: string, reports: string[], flags: string[], path: string, reportPaths: string[]}}
         */
        trustedMetadataUnder: (untrustedGroupPath) => {
            const trustedPath = helpers.trust(path.join(untrustedGroupPath, METADATA_FILENAME));
            const content = fs.readFileSync(trustedPath).toString();
            core.debug(untrustedGroupPath + ' content=' + content);

            const untrustedMetadata = JSON.parse(content);
            const trustedGroupPath = path.dirname(trustedPath);
            const trustedReportPaths = untrustedMetadata.reports.map(r => helpers.trust(r));

            return {
                name: untrustedMetadata.name,
                format: untrustedMetadata.format,
                reports: trustedReportPaths,
                flags: untrustedMetadata.flags,
                path: withTrailingSeparator(trustedGroupPath),
                reportPaths: trustedReportPaths.map(trustedFp => path.join(trustedGroupPath, trustedFp)),
            };
        }
    };

    return helpers;
}
