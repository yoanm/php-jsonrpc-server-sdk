import path from "path";

const core = require('@actions/core'); // @TODO move to 'imports from' when moved to TS !

const SDK = require("../index");

/**
 * @param {import('./interfaces').Metadata[]} mdList
 *
 * @return {MetadataJson}
 */
export function convertMetadataListToMetadataJson(mdList) {
    return {
        name: SDK.array.itemsPropertyList(mdList, 'name'),
        format: SDK.array.itemsPropertyList(mdList, 'format'),
        reports: SDK.array.itemsPropertyList(mdList, 'reports'),
        flags: SDK.array.itemsPropertyList(mdList, 'flags'),
        path: SDK.array.itemsPropertyList(mdList, 'path'),
        artifact: mdList[0].artifact ? SDK.array.itemsPropertyList(mdList, 'artifact') : undefined,
    }
}

/**
 * @param {MetadataJson} metadataJson
 * @param {string} format
 * @param {string} glueString
 *
 * @return {MetadataString}
 */
export function convertMetadataJsonToMetadataString(metadataJson, format, glueString) {
    const isGlobStringFormat = 'glob-string' === format;

    return {
        name: SDK.array.mergeStringList(metadataJson.name).join(glueString),
        format: SDK.array.mergeStringList(metadataJson.format).join(glueString),
        reports: SDK.array.mergeListOfList(metadataJson.reports).join(isGlobStringFormat ? '\n' : glueString),
        flags: SDK.array.mergeListOfList(metadataJson.flags).join(glueString),
        path: SDK.array.mergeStringList(metadataJson.path).join(isGlobStringFormat ? '\n' : glueString),
        artifact: metadataJson.artifact ? SDK.array.mergeStringList(metadataJson.artifact).join('\n') : undefined,
    }
}

/**
 * @param {import('./interfaces').Metadata[][]} trustedMetadataListOfList
 * @param {string} format
 * @param {string} glueString
 * @param {boolean} artifactModeEnabled
 *
 * @return {ActionOutputData}
 */
export function convertMetadataListToFindActionOutput(trustedMetadataListOfList, format, glueString, artifactModeEnabled) {
    const metadataJsonList = trustedMetadataListOfList.map(trustedMetadataList => convertMetadataListToMetadataJson(trustedMetadataList));

    /** @type {string[]} */
    const reportPathList = [];
    for (const metadataJson of metadataJsonList) {
        [...metadataJson.reports.entries()].forEach(
            ([key, pList]) => pList.forEach(
                p => reportPathList.push(artifactModeEnabled ? path.join(metadataJson.artifact[key], p) : p)
            )
        );
    }

    /** @type {string} */
    const paths = SDK.array.mergeStringList(reportPathList).join('\n');

    /** @type {MultiGroupOutput<MetadataJson>} */
    const res = {
        count: reportPathList.length,
        paths: paths,
        list: metadataJsonList
    };
    if ('json' !== format) {
        /** `string` or `glob-string` format **/
        return {
            ...res,
            list: res.list.map(jsonItem => convertMetadataJsonToMetadataString(jsonItem, format, glueString))
        };
    }

    /** `json` format **/
    return res;
}
