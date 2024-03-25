const core = require('@actions/core'); // @TODO move to 'imports from' when moved to TS !

import * as arrayHelper from './array';

export const ALLOWED_MERGED_VALUES = ['name', 'flags', 'format', 'artifact'];

/**
 * @param {import('./interfaces').Metadata[]} list
 * @param {MergeField} mergeField
 *
 * @return {import('./interfaces').Metadata[][]}
 */
function groupByFieldValue(list, mergeField) {
    /** @type {Record<string, import('./interfaces').Metadata[]>} */
    const fieldValueToItemsMap = {};
    for (const metadata of list) {
        const key = Array.isArray(metadata[mergeField]) ? arrayHelper.mergeStringList(metadata[mergeField]).sort().join('#') : metadata[mergeField];
        if (!fieldValueToItemsMap[key]) {
            fieldValueToItemsMap[key] = [];
        }
        fieldValueToItemsMap[key].push(metadata)
    }

    return Object.values(fieldValueToItemsMap);
}

/**
 * @param {import('./interfaces').Metadata[]} list
 * @param {(MergeField & string)[]} mergeByFieldList
 *
 * @return {import('./interfaces').Metadata[][]}
 */
export function groupMetadataList(list, mergeByFieldList) {
    if (mergeByFieldList.length === 0 || list.length === 0) {
        return [list];
    }
    const mergeByFieldListCopy = [...mergeByFieldList];
    const mergeField = mergeByFieldListCopy.shift();

    if (!ALLOWED_MERGED_VALUES.includes(mergeField)) {
        core.warning('"' + mergeField + '" is not allowed as merge field, ignored ! Allowed ' + ALLOWED_MERGED_VALUES.join(','));

        return groupMetadataList(list, mergeByFieldListCopy);
    }
    if ('artifact' === mergeField && !list[0].hasOwnProperty('artifact')) {
        core.warning('Merge on "artifact" field while undefined, ignored !');

        return groupMetadataList(list, mergeByFieldListCopy);
    }
    core.info('Merge by ' + mergeField);
    /** @type {import('./interfaces').Metadata[][]} */
    const newList = [];
    for (const metadataList of groupByFieldValue(list, mergeField)) {
        groupMetadataList(metadataList, mergeByFieldListCopy).forEach(metadataList => newList.push(metadataList));
    }

    return newList;
}
