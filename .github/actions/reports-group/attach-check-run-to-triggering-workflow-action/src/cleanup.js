const github = require('@actions/github'); // @TODO move to 'imports from' when moved to TS !
const core = require('@actions/core');

// @TODO Find a way to retrieve current job (add the jobId as state to re-use it here !) and forward the annotations as Check run `details_text`

async function run() {
    if (core.getState('check-run-already-concluded').length > 0) {
        core.info('Check run already concluded, skipping check run update');
    }
    const checkRunId = core.getState('check-run-id');
    if (checkRunId.length === 0) {
        throw new Error('Unable to retrieve check run id !');
    }

    /** INPUTS **/
    const jobStatus = core.getInput('job-status', {required: true});
    const githubToken = core.getInput('github-token', {required: true});

    const requestParams = await core.group(
        'Build API params',
        async () => {
            return {
                conclusion: jobStatus,
                // Url path parameters
                owner: github.context.repo.owner,
                repo: github.context.repo.repo,
                check_run_id: checkRunId
            };
        }
    );
    core.debug('API params=' + JSON.stringify(requestParams));

    const apiResponse = await core.group('Conclude check-run ', async () => {
        /** @type {OctokitInterface} */
        const octokit = github.getOctokit(githubToken);

        // @TODO Move back to `octokit.rest.checks.update()`
        return octokit.request('PATCH /repos/{owner}/{repo}/check-runs/{check_run_id}', requestParams);
    });
    core.debug('API call to ' +apiResponse.url + ' => HTTP ' + apiResponse.status);
}

// No need to wrap cleanup with try/catch, error are ignored anyway
run();
