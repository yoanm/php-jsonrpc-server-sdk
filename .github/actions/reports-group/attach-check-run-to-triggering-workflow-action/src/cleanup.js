const github = require('@actions/github'); // @TODO move to 'imports from' when moved to TS !
const core = require('@actions/core');

const {GITHUB_REPOSITORY} = process.env;

async function run() {
    if (core.getState('check-run-is-concluded').length) {
        core.info('Check run already concluded, skipping check run update');
    }

    const [repoOwner, repoName] = GITHUB_REPOSITORY.split('/');
    /** INPUTS **/
    const jobStatus = core.getInput('job-status', {required: true});
    const checkRunId = core.getState('check-run-id');
    if (checkRunId.length === 0) {
        throw new Error('Unable to retrieve check run id !');
    }
    const githubToken = core.getInput('github-token', {required: true});

    const requestParams = await core.group(
        'Build API params',
        async () => {
            return {
                conclusion: jobStatus,
                owner: repoOwner,
                repo: repoName,
                check_run_id: checkRunId
            };
        }
    );
    core.debug('API params=' + JSON.stringify(requestParams));

    const apiResponse = await core.group('Retrieve ', async () => {
        const octokit = github.getOctokit(githubToken);

        // @TODO Move back to `octokit.rest.checks.update()`
        const res = await octokit.request('PATCH /repos/{owner}/{repo}/check-runs/{check_run_id}', requestParams);

        core.info('TMP DEBUG0 ' + JSON.stringify(res));

        return res;
    });
    core.info('TMP DEBUG' + JSON.stringify(apiResponse));
}

run();
