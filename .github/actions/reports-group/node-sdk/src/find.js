import path from "path";
import core from "@actions/core";

import {METADATA_FILENAME} from "./constants";
import * as globHelper from "./glob-helper";
import * as pathHelper from "./path-helper";

export async function groupPaths(pattern, options = undefined) {
    const absWorkspace = path.resolve('.');

    const list = [];
    for await (const fp of metadataPaths(pattern, options)) {
        const cleanPath = pathHelper.relativeTo(absWorkspace, path.dirname(fp));
        core.info('Found a reports group directory at ' + cleanPath);
        list.push(cleanPath);
    }

    return list;
}

export async function metadataPaths(pattern, options = undefined) {
    const finalPattern = pattern.split('\n').map(item => path.join(item.trim(), '**', METADATA_FILENAME)).join('\n');
    core.debug('findGroupPaths glob: ' + pattern);

    return globHelper.lookup(finalPattern, options);
}
