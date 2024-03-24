/**
 * @param {string[]} list
 *
 * @return {string[]}
 */
export const arrayUnique = (list) => ([...new Set(list)]);

/**
 * @type {MetadataKeyMapper}
 */
export const itemsPropertyList = (list, property) => list.map(m => m[property])

/**
 * @param {string[]} list
 *
 * @return {string[]}
 */
export const mergeStringList = (list) => arrayUnique(list);

/**
 @param {string[][]} list

 @return {string[]}
 */
export const mergeListOfList = (list) => arrayUnique(list.flat());
