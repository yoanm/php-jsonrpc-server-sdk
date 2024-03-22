const github = require('@actions/github'); // @TODO move to 'imports from' when moved to TS !
const core = require('@actions/core');

async function run() {
    const {GITHUB_REPOSITORY: repository} = process.env;
    const [repoOwner, repoName] = repository.split('/');
    /** INPUTS **/
    const commitSha = core.getInput('commit-sha', {required: true});
    const checkName = core.getInput('name', {required: true});
    const githubToken = core.getInput('github-token', {required: true});

    // Following inputs are not required and may not have any value attached !
    const checkStatus = core.getInput('status');
    const checkConclusion = core.getInput('conclusion');
    const externalId = core.getInput('external-id');
    const startedAt = core.getInput('started-at');
    const completedAt = core.getInput('completed-at');
    const detailsUrl = core.getInput('details-url');
    const outputTitle = core.getInput('output');
    const outputSummary = core.getInput('output-summary');

    const requestParams = await core.group(
        'Build API params',
        async () => {
            const res = {
                name: checkName,
                head_sha: commitSha,
                details_url: undefinedIfEmpty(detailsUrl),
                external_id: undefinedIfEmpty(externalId),
                status: undefinedIfEmpty(checkStatus),
                conclusion: undefinedIfEmpty(checkConclusion),
                started_at: undefinedIfEmpty(startedAt),
                completed_at: undefinedIfEmpty(completedAt),
                owner: repoOwner,
                repo: repoName
            };
            if (!isEmpty(outputTitle) || !isEmpty(outputSummary)) {
                res.output = {title: undefinedIfEmpty(outputTitle), summary: undefinedIfEmpty(outputSummary)};
            }
        }
    );
    core.debug('API params=' + JSON.stringify(requestParams));

    const apiResponse = await core.group('Call API', async () => {
        const octokit = github.getOctokit(githubToken);

        return octokit.request('POST /repos/{owner}/{repo}/check-runs', requestParams);
    });
    core.info('TMP DEBUG' + JSON.stringify(apiResponse));

    core.setOutput('check-run-id', apiResponse.data.id);
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
