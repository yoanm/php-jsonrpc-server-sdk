const core = require('@actions/core');

async function run() {
    Object.entries(process.env).forEach(([key, value]) => {
        if (key.startsWith('INPUT_') || key.startsWith('GITHUB_')) {
            core.info(key + '=' + JSON.stringify(value));
        }
    });
}

run();
