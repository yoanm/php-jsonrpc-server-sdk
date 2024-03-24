/**
 * @param {import('./interfaces').Metadata[]} list
 * @param {string[]} forbiddenGroupList
 * @param {string[]} forbiddenFormatList
 * @param {string[]} forbiddenFlagList
 * @param {string[]} forbiddenPathList
 * @param {string[]} allowedGroupList
 * @param {string[]} allowedFormatList
 * @param {string[]} allowedFlagList
 * @param {string[]} allowedPathList
 *
 * @return {import('./interfaces').Metadata[]}
 */
export function filterMetadataList(
    list,
    forbiddenGroupList, forbiddenFormatList, forbiddenFlagList, forbiddenPathList,
    allowedGroupList, allowedFormatList, allowedFlagList, allowedPathList
) {
    if (
        !forbiddenGroupList.length && !forbiddenFormatList.length && !forbiddenFlagList.length && !forbiddenPathList.length &&
        !allowedGroupList.length && !allowedFormatList.length && !allowedFlagList.length && !allowedPathList.length
    ) {
        // Nothing to filter in or out
        return list;
    }

    const tmpList = [];
    for (const metadata of list) {
        // Forbidden before Allowed !
        if (
            // Forbidden
            (forbiddenGroupList.length > 0 && forbiddenGroupList.includes(metadata.name))
            || (forbiddenFormatList.length > 0 && forbiddenFormatList.includes(metadata.format))
            || (forbiddenFlagList.length > 0 && forbiddenFlagList.filter(allowedFlag => metadata.flags.includes(allowedFlag)).length > 0)
            || (forbiddenPathList.length > 0 && forbiddenPathList.filter(allowedPath => metadata.path.includes(allowedPath)).length > 0)
            // Allowed
            || (allowedGroupList.length > 0 && !allowedGroupList.includes(metadata.name))
            || (allowedFormatList.length > 0 && !allowedFormatList.includes(metadata.format))
            || (allowedFlagList.length > 0 && allowedFlagList.filter(allowedFlag => metadata.flags.includes(allowedFlag)).length === 0)
            || (allowedPathList.length > 0 && allowedPathList.filter(allowedPath => metadata.path.includes(allowedPath)).length === 0)
        ) {
            continue;
        }
        tmpList.push(metadata);
    }

    return tmpList;
}
