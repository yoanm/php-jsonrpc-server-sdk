const github = require('@actions/github'); // @TODO move to 'imports from' when moved to TS !
const core = require('@actions/core');

const {GITHUB_REPOSITORY} = process.env;

async function run() {
    const [repoOwner, repoName] = GITHUB_REPOSITORY.split('/');
    /** INPUTS **/
    const commitSha = core.getInput('commit-sha', {required: true});
    const checkName = core.getInput('name', {required: true});
    const githubToken = core.getInput('github-token', {required: true});
    const jobStatus = core.getInput('job-status', {required: true});

    // Following inputs are not required and may not have any value attached !
    const externalId = core.getInput('external-id');
    const startedAt = core.getInput('started-at');
    const completedAt = core.getInput('completed-at');
    const detailsUrl = core.getInput('details-url');
    const outputTitle = core.getInput('output');
    const outputSummary = core.getInput('output-summary');

    const isSuccessfulJobAsOfNow = 'success' === jobStatus;

    const requestParams = await core.group(
        'Build API params',
        async () => {
            const res = {
                name: checkName,
                head_sha: commitSha,
                details_url: undefinedIfEmpty(detailsUrl),
                external_id: undefinedIfEmpty(externalId),
                status: isSuccessfulJobAsOfNow ? 'in_progress' : 'completed',
                conclusion: isSuccessfulJobAsOfNow ? undefined : jobStatus,
                started_at: undefinedIfEmpty(startedAt),
                completed_at: undefinedIfEmpty(completedAt),
                owner: repoOwner,
                repo: repoName
            };
            if (!isEmpty(outputTitle) || !isEmpty(outputSummary)) {
                res.output = {title: undefinedIfEmpty(outputTitle), summary: undefinedIfEmpty(outputSummary)};
            }

            return res;
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
