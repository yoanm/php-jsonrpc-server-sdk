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
    if (untrustedPath.length && path.sep !== untrustedPath[untrustedPath.length - 1]) {
        return untrustedPath + path.sep
    }

    return untrustedPath;
}

export function trustedPathHelpers() {
    return trustFrom(GITHUB_WORKSPACE);
}

/**
 * @param {string} workspacePath
 * @param {string} untrustedPath
 * @param {string} normalizedPath
 *
 * @return {string}
 */
function formatErrorDetails(workspacePath, untrustedPath, normalizedPath) {
    return ' Workspace: "' + workspacePath + '"'
        + ' Path: "' + normalizedPath + '"'
        + (untrustedPath !== normalizedPath ? ' (provided: "' + untrustedPath + '")' : '');
}


function avoidPoisonNullBytesAttack(untrustedPath) {
    if (untrustedPath.indexOf('\0') !== -1) {
        throw new Error('Potential "Poison Null Bytes" attack detected !');
    }
}
function avoidRelativePathAttack(workspacePath, untrustedPath) {
    const normalizedPath = path.resolve(untrustedPath);
    if (normalizedPath.indexOf(workspacePath) !== 0) {
        throw new Error(
            'Potential "Relative Path" attack detected !\n' + formatErrorDetails(workspacePath, untrustedPath, normalizedPath)
        );
    }
}

function mustBeStrictlyIn(workspacePath, untrustedPath) {
    const normalizedPath = path.resolve(untrustedPath);
    if (path.dirname(normalizedPath) !== workspacePath) {
        throw new Error(
            'Path should lead to the workspace directory only !\n' + formatErrorDetails(workspacePath, untrustedPath, normalizedPath)
        );
    }
}

function trustFrom(workspacePath, zeroDepth = false) {
    // Ensure workspace path is ok
    avoidPoisonNullBytesAttack(workspacePath)
    if (!path.isAbsolute(workspacePath)) {
        throw new Error('Workspace path must be an absolute path');
    }
    const helpers = {
        /**
         * @param {string} untrustedPath
         *
         * @return {string}
         */
        trust: (untrustedPath) => {
            avoidPoisonNullBytesAttack(untrustedPath);
            if (zeroDepth) {
                mustBeStrictlyIn(workspacePath, untrustedPath);
            } else {
                avoidRelativePathAttack(workspacePath, untrustedPath);
            }

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
         *
         * @returns {import('./interfaces').Metadata[]}
         */
        trustedMetadataUnder: (untrustedGroupPath) => {
            const trustedPath = helpers.trust(path.join(untrustedGroupPath, METADATA_FILENAME));
            const content = fs.readFileSync(trustedPath).toString();
            core.debug(untrustedGroupPath + ' content=' + content);

            const untrustedMetadata = /** @type {Metadata} */JSON.parse(content);
            const trustedGroupPath = path.dirname(trustedPath);
            // Ensure `reports` hasn't been tampered with ! (may lead to files outside the directory)
            const trustedReportPathsConverter = trustFrom(path.resolve(trustedGroupPath), true);

            return /** @type {import('./interfaces').Metadata[]} */{
                name: untrustedMetadata.name,
                format: untrustedMetadata.format,
                reports: untrustedMetadata.reports.map(r => trustedReportPathsConverter.trust(path.join(trustedGroupPath, r))),
                flags: untrustedMetadata.flags,
                path: withTrailingSeparator(trustedGroupPath),
            };
        }
    };

    return helpers;
}
