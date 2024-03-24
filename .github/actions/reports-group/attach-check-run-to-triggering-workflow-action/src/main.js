const {getOctokit} = require('@actions/github'); // @TODO move to 'imports from' when moved to TS !
const core = require('@actions/core');

const ghaHelpers = require('../node-gha-helpers');

const formatMarkdownUrl = (title, link) => '<a href="' + link + '" target="blank">' + title + '</a>';

async function run() {
    /** INPUTS **/
    const githubToken = core.getInput('github-token', {required: true});
    const jobStatus = core.getInput('job-status', {required: true});
    const checkName = core.getInput('name');
    const failsOnTriggeringWorkflowFailure = core.getBooleanInput('fails-on-triggering-workflow-failure', {required: true});

    const isSuccessfulJobAsOfNow = 'success' === jobStatus;
    const octokit = /** @type {OctokitInterface} */getOctokit(githubToken);

    const requestParams = await core.group(
        'Build API params',
        async () => {
            const currentWorkflowContext = ghaHelpers.getContext();
            const triggeringWorkflowContext = ghaHelpers.triggeringWorkflow.getContext();
            if (!triggeringWorkflowContext.commitSha) {
                throw new Error('Unable to guess the commit SHA !');
            }
            const currentJob = await ghaHelpers.fetchCurrentJob(octokit);
            const nowDate = (new Date()).toISOString();
            const summaryRedirectMrkLink = formatMarkdownUrl(
                '**' + currentWorkflowContext.workflowName + (!currentJob ? '' : '** → **' + currentJob.name )+ '** ' + (!currentJob ? 'workflow' : 'job' ),
                !currentJob ? currentWorkflowContext.workflowRunUrl : ghaHelpers.buildWorkflowJobRunUrl(currentJob, triggeringWorkflowContext.prNumber)
            );

            return {
                name: !!checkName ? checkName : (currentJob?.name ?? currentWorkflowContext.workflowName + ' Check run'),
                head_sha: triggeringWorkflowContext.commitSha,
                started_at: !currentJob ? nowDate : currentJob.started_at,
                conclusion: isSuccessfulJobAsOfNow ? undefined : jobStatus,
                completed_at: isSuccessfulJobAsOfNow ? undefined : nowDate,
                status: isSuccessfulJobAsOfNow ? 'in_progress' : 'completed',
                output: {
                    title: '🔔 ' + currentWorkflowContext.workflowName,
                    summary: '🪢 Check added by ' + summaryRedirectMrkLink,
                },
                external_id: triggeringWorkflowContext.runId,
                details_url: (!currentJob
                    ? currentWorkflowContext.workflowRunUrl
                    : ghaHelpers.buildWorkflowJobRunUrl(currentJob, triggeringWorkflowContext.prNumber)
                ),
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

run().catch(e => {
    core.info('Error caught and ignored ' + e.message);
    core.debug('Error=' + JSON.stringify(e));
});
