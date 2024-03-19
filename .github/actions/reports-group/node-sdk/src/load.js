import fs from 'fs';
import path from 'path';

export async function metadataFile(groupPath) {
    const content = await fs.readFile(groupPath);

    const metadata = JSON.parse(content);

    return {...metadata, path: groupPath, reportPaths: metadata.reports.map(r => path.join(groupPath, r))};
}
