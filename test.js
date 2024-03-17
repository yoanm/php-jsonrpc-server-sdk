          const {resolve: pathResolve, dirname: pathDirname} = require('path');
          const fs = require('fs');

          const { ROOT } = process.env;

          const absWorkspace = pathResolve(ROOT);

          function enhanceMetadataMapWithGroup(original) {
            const counterByDirectory = {};
            for (const data of original) {
              let directory = data.path;
              while (directory.length > 0 && PATH !== directory) {
                counterByDirectory[directory] = (counterByDirectory[directory] ?? 0) + 1;
                directory = pathDirname(directory);
              }
            }
            core.info(`counterByDirectory='${JSON.stringify(counterByDirectory)}'`);
            const directorySetByCounter = [];
            for (const directory of Object.keys(counterByDirectory)) {
              const counter = counterByDirectory[directory];
              if (!Array.isArray(directorySetByCounter[counter])) {
                directorySetByCounter[counter] = new Set();
              }
              directorySetByCounter[counter].add(directory);
            }
            core.info(`directorySetByCounter='${JSON.stringify(directorySetByCounter)}'`);
            if (directorySetByCounter.length > 0) {
              const mostKnownDirectories = directorySetByCounter[directorySetByCounter.length - 1].keys();
              const groupDirectories = mostKnownDirectories.filter(v => {
                // Remove directories shared by at least another one
                return undefined === mostKnownDirectories.find(v2 => v !== v2 && v === v2.substring(0, v.length));
              });

              core.debug(`groupDirectories='${JSON.stringify(groupDirectories)}'`);
            }

            return original;
          }

          const metadataFileList = await core.group(`Look for metadata files under '${ROOT}'`, async () => {
            const res = [];
            const globber = await glob.create(`${absWorkspace}/**/.reports-group-metadata.json`);
            for await (const file of globber.globGenerator()) {
              const filepath = file.replace(`${absWorkspace}/`, '');
              core.info(`Found '${filepath}'`);

              res.push(filepath);
            }
            return res;
          });
          core.debug(`metadataFileList='${JSON.stringify(metadataFileList)}'`);

          const metadataMap = await core.group('Build metadata', async () => {
            const res = [];
            for (const file of metadataFileList) {
              core.debug(`file='${file}'`);

              const fullPath = pathDirname(file);

              core.info(`Process ${fullPath} directory`);

              const globber = await glob.create(`${fullPath}/*-report-[0-99]`);
              const metadataContent = fs.readFileSync(`${ROOT}/${file}`);
              const metadata = JSON.parse(metadataContent);

              const item = {...metadata, path: fullPath};

              core.debug(`item='${JSON.stringify(item)}'`);

              res.push(item);
            }

            const metadataMap = enhanceMetadataMapWithGroup(res);

            const metadataMapString = JSON.stringify(metadataMap);
            core.debug(`metadataMap='${metadataMapString}'`);

            core.setOutput("map", metadataMapString)

            return metadataMap;
          });

          await core.group('Build matrix', async () => {
            const matrixInclude = [];
            for (const groupPath of Object.keys(metadataMap)) {
              const metadata = metadataMap[groupPath];
              const item = {
                ...metadata,
                path: groupPath,
                reports: JSON.stringify(metadata.reports),
                flags: JSON.stringify(metadata.flags),
              };

              core.debug(`item='${JSON.stringify(item)}'`);

              matrixInclude.push(item);
            }

            const matrix = {include: matrixInclude};

            const matrixString = JSON.stringify(matrix);
            core.debug(`matrix='${matrixString}'`);

            core.setOutput("matrix", matrixString);
          });

          await core.group('Build full report list', async () => {
            const reportList = [];
            or (const groupPath of Object.keys(metadataMap)) {
              const metadata = metadataMap[groupPath];
              const item = metadata.reports.map(fp => `${groupPath}/${fp}`);

              core.debug(`item='${JSON.stringify(item)}'`);

              reportList.push(item);
            }

            const reportListString = JSON.stringify(reportList);
            core.debug(`reportList='${reportListString}'`);

            core.setOutput("reports", reportListString);
          });
