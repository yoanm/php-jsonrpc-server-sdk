const {getOctokit} = require('@actions/github'); // @TODO move to 'imports from' when moved to TS !
const core = require('@actions/core');

const {GITHUB_REPOSITORY} = process.env;

const ghaHelpers = require('../node-gha-helpers');

async function run() {
    /** INPUTS **/
    const githubToken = core.getInput('github-token', {required: true});
    const jobStatus = core.getInput('job-status', {required: true});
    const checkName = core.getInput('name');
    const failsOnTriggeringWorkflowFailure = core.getBooleanInput('fails-on-triggering-workflow-failure', {required: true});

    const isSuccessfulJobAsOfNow = 'success' === jobStatus;
    const octokit = getOctokit(githubToken);

    const requestParams = await core.group(
        'Build API params',
        async () => {
            const currentWorkflowContext = ghaHelpers.getContext();
            const triggeringWorkflowContext = ghaHelpers.triggeringWorkflow.getContext();
            if (!triggeringWorkflowContext.commitSha) {
                throw new Error('Unable to guess the commit SHA !');
            }
            const currentJob = await ghaHelpers.fetchCurrentJob(octokit);

            const startedAt = (new Date()).toISOString();
            const prLink = (undefined !== triggeringWorkflowContext.prNumber ? '?pr=' + triggeringWorkflowContext.prNumber : '');
            const currentWorkflowUrl = currentWorkflowContext.serverUrl + '/' + GITHUB_REPOSITORY + '/actions/runs/' + currentWorkflowContext.runId + prLink;
            const outputSummary = '🪢 Check added by '
                + (currentJob ? '<a href="' + currentJob.html_url + prLink + '" target="blank">**' + currentJob.name + '**</a>' : '')
                + (currentJob ? ' (' : '') + '<a href="' + currentWorkflowUrl + '" target="blank">**' + currentWorkflowContext.workflowName + '** workflow</a>' + (currentJob ? ')' : '')
            ;

            return {
                name: !!checkName ? checkName : (currentJob?.name ?? currentWorkflowContext.workflowName + ' Check run'),
                head_sha: triggeringWorkflowContext.commitSha,
                //details_url: detailsUrl,
                external_id: triggeringWorkflowContext.runId,
                status: isSuccessfulJobAsOfNow ? 'in_progress' : 'completed',
                output: {
                    title: '🔔 ' + currentWorkflowContext.workflowName,
                    summary: outputSummary,
                },
                // Conclusion
                conclusion: isSuccessfulJobAsOfNow ? undefined : jobStatus,
                started_at: startedAt,
                completed_at: isSuccessfulJobAsOfNow ? undefined : startedAt,
                // Url path parameters
                owner: currentWorkflowContext.repositoryOwner,
                repo: currentWorkflowContext.repositoryName
            };
        }
    );
    core.debug('API params=' + JSON.stringify(requestParams));

    const apiResponse = await core.group('Create check-run', async () => {
        // @TODO Move back to `octokit.rest.checks.create()`
        return octokit.request('POST /repos/{owner}/{repo}/check-runs', requestParams);
    });
    core.debug('API call to ' +apiResponse.url + ' => HTTP ' + apiResponse.status);

    core.setOutput('check-run-id', apiResponse.data.id);
    core.saveState('check-run-id', apiResponse.data.id); // In order to use it during POST hook
    if (isSuccessfulJobAsOfNow) {
        core.saveState('check-run-already-concluded', 'yes'); // In order to use it during POST hook
    }
    if (failsOnTriggeringWorkflowFailure && !isSuccessfulJobAsOfNow) {
        core.setFailed('Triggering workflow status is "' + jobStatus + '" !');
    }
}

run();
