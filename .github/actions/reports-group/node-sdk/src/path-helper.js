const path = require('path'); // @TODO move to 'imports from' when moved to TS !
const {GITHUB_WORKSPACE} = process.env;

export function relativeTo(root, ...segments) {
    const resolvedPath = path.resolve(...segments);

    return resolvedPath.startsWith(root) ? resolvedPath.replace(root + path.sep, '') : resolvedPath;
}

export function relativeToGHWorkspace(...segments) {
    return relativeTo(GITHUB_WORKSPACE, ...segments);
}
