const github = require('@actions/github'); // @TODO move to 'imports from' when moved to TS !
const core = require('@actions/core');

const {GITHUB_REPOSITORY, GITHUB_SERVER_URL, GITHUB_RUN_ID, GITHUB_JOB} = process.env;

async function run() {
    /** INPUTS **/
    const commitSha = core.getInput('commit-sha', {required: true});
    const checkName = core.getInput('name', {required: true});
    const githubToken = core.getInput('github-token', {required: true});
    const jobStatus = core.getInput('job-status', {required: true});

    const isSuccessfulJobAsOfNow = 'success' === jobStatus;

    const requestParams = await core.group(
        'Build API params',
        async () => {
            const [repoOwner, repoName] = GITHUB_REPOSITORY.split('/');
            const externalId = GITHUB_RUN_ID;
            const startedAt = (new Date()).toISOString();
            //${{ ( 'workflow_run' == github.event_name && 'pull_request' == github.event.workflow_run.event && github.event.workflow_run.pull_requests[0] && github.event.workflow_run.pull_requests[0].number) || ('pull_request' == github.event_name && github.event.number) || null }}
            const prNumber = 'workflow_run' === github.context.eventName && 'pull_request' === github.context.payload.event && github.context.payload.pull_requests[0]?.number
                ? github.context.payload.pull_requests[0]?.number
                : undefined
            ;
            const detailsUrl = GITHUB_SERVER_URL + '/' + GITHUB_REPOSITORY + '/actions/runs/' + GITHUB_RUN_ID + '/job/' + GITHUB_JOB + (undefined !== prNumber ? '?pr=' + prNumber : '');
            const outputTitle = 'My title';
            const outputSummary = 'My summary';
            const outputText = 'My text';

            return {
                name: checkName,
                head_sha: commitSha,
                details_url: detailsUrl,
                external_id: externalId,
                status: isSuccessfulJobAsOfNow ? 'in_progress' : 'completed',
                output: {
                    title: outputTitle,
                    summary: outputSummary,
                    text: outputText,
                },
                // Conclusion
                conclusion: isSuccessfulJobAsOfNow ? undefined : jobStatus,
                started_at: startedAt,
                completed_at: isSuccessfulJobAsOfNow ? undefined : startedAt,
                // Url path parameters
                owner: repoOwner,
                repo: repoName
            };
        }
    );
    core.debug('API params=' + JSON.stringify(requestParams));

    const apiResponse = await core.group('Call API', async () => {
        const octokit = github.getOctokit(githubToken);

        // @TODO Move back to `octokit.rest.checks.create()`
        const res = await octokit.request('POST /repos/{owner}/{repo}/check-runs', requestParams);

        core.info('TMP DEBUG0 ' + JSON.stringify(res));

        return res;
    });
    core.info('TMP DEBUG' + JSON.stringify(apiResponse));

    core.setOutput('check-run-id', apiResponse.data.id);
    core.saveState('check-run-id', apiResponse.data.id); // In order to use it during POST hook
    if (true === isSuccessfulJobAsOfNow) {
        core.saveState('check-run-already-concluded', 'yes'); // In order to use it during POST hook
    }
}

/**
 * @param {string} val
 *
 * @returns {string|undefined}
 */
function undefinedIfEmpty(val) {
    return !isEmpty(val) ? val : undefined
}
/**
 * @param {string} val
 *
 * @returns {boolean}
 */
function isEmpty(val) {
    return val.trim().length === 0;
}

run();
