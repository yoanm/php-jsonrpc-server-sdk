const github = require('@actions/github'); // @TODO move to 'imports from' when moved to TS !
const core = require('@actions/core');

async function run() {
    const checkRunId = core.getState('check-run-id');
    if (checkRunId.length === 0) {
        throw new Error('Unable to retrieve check run id !');
    }

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
}

run();
