const fs = require('fs'); // @TODO move to 'imports from' when moved to TS !
const path = require('path'); // @TODO move to 'imports from' when moved to TS !

const core = require('@actions/core'); // @TODO move to 'imports from' when moved to TS !

export async function metadataFile(fp) {
    const content = fs.readFileSync(fp);
    core.debug(fp + ' content=' + content);

    const metadata = JSON.parse(content);
    const groupPath = path.dirname(fp);

    return {
        ...metadata,
        path: groupPath,
        reportPaths: metadata.reports.map(r => path.join(groupPath, r))
    };
}
