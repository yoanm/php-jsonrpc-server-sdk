const fs = require('fs'); // @TODO move to 'imports from' when moved to TS !
const path = require('path'); // @TODO move to 'imports from' when moved to TS !

export async function metadataFile(fp) {
    const content = fs.readFileSync(fp);
    console.log('DEBUG metadataFile(' + fp + ') => content=' + content);
    const metadata = JSON.parse(content);
    const groupPath = path.dirname(fp);
    console.log('DEBUG metadataFile(' + fp + ') => group path=' + groupPath);

    const res = {...metadata, path: groupPath, reportPaths: metadata.reports.map(r => path.join(groupPath, r))};
    console.log('DEBUG metadataFile(' + fp + ') => res=' + JSON.stringify(res));

    return res;
}
