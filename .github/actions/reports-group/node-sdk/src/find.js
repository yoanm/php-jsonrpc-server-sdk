const core = require('@actions/core'); // @TODO move to 'imports from' when moved to TS !
const path = require('path'); // @TODO move to 'imports from' when moved to TS !

const {METADATA_FILENAME} = require('./constants');
const globHelper = require('./glob-helper');
const pathHelper = require('./path-helper');

export async function groupPaths(globPattern, options = undefined) {
    const absWorkspace = path.resolve('.');
    const list = [];
    for (const fp of await metadataPaths(globPattern, options)) {
        list.push(pathHelper.relativeTo(absWorkspace, path.dirname(fp)));
    }

    return list;
}

export async function metadataPaths(globPattern, options = undefined) {
    const finalPattern = globPattern.split('\n').map(item => path.join(item.trim(), '**', METADATA_FILENAME)).join('\n');
    core.debug('findGroupPaths glob: ' + globPattern);

    const list = [];
    for await (const fp of globHelper.lookup(finalPattern, options)) {
        list.push(fp);
    }

    return list;
}
