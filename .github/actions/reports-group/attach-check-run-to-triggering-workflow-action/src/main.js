const github = require('@actions/github'); // @TODO move to 'imports from' when moved to TS !
const core = require('@actions/core');

const {GITHUB_REPOSITORY} = process.env;

/**
 * @returns {number|undefined}
 */
function guessPrNumber() {
    if ('pull_request' === github.context.eventName) {
        return github.context.payload.number
    }
    if ('workflow_run' === github.context.eventName) {
        return 'pull_request' === github.context.payload.workflow_run.event
            ? github.context.payload.pull_requests[0]?.number
            : undefined
        ;
    }

    return undefined;
}

/**
 * @returns {string|undefined}
 */
function guessCommitSha() {
    if ('pull_request' === github.context.eventName) {
        return github.context.payload.pull_request.head.sha;
    }
    if ('push' === github.context.eventName) {
        return github.context.payload.after;
    }
    if ('workflow_run' === github.context.eventName && ['pull_request', 'push'].includes(github.context.payload.workflow_run.event)) {
        return github.context.payload.workflow_run.head_sha;
    }

    throw new Error('Unable to guess the commit SHA !');
}

/**
 * @returns {string}
 */
function guessTriggeringWorkflowName() {
    if ('workflow_run' === github.context.eventName) {
        return github.context.payload.workflow.name;
    }

    return github.context.workflow;
}

/**
 * @returns {string}
 */
function guessRunId() {
    if ('workflow_run' === github.context.eventName) {
        return github.context.payload.id.toString();
    }

    return github.context.runId.toString();
}

async function getWorkflowJobsForRunId(octokit, owner, repo, runId) {
    return octokit.paginate(
        'GET /repos/{owner}/{repo}/actions/runs/{run_id}/jobs',
        {
            //filter: 'latest',
            // Url path parameters
            owner: owner,
            repo: repo,
            run_id: runId
        }
    );
}

async function run() {
    /** INPUTS **/
    const checkName = core.getInput('name', {required: true});
    const githubToken = core.getInput('github-token', {required: true});
    const jobStatus = core.getInput('job-status', {required: true});

    const isSuccessfulJobAsOfNow = 'success' === jobStatus;
    const octokit = github.getOctokit(githubToken);

    const requestParams = await core.group(
        'Build API params',
        async () => {
            const repoInfo = github.context.repo;
            const triggeringWorkflowRunId = guessRunId();
            //core.info('TMP DEBUG context=' + JSON.stringify(github.context));
            //const jobsForCurrentWorkflow = await getWorkflowJobsForRunId(octokit, repoInfo.owner, repoInfo.repo, github.context.runId);
            //core.info('TMP DEBUG jobsForCurrentWorkflow=' + JSON.stringify(jobsForCurrentWorkflow.map(v => {v.steps = '-_-'; return v;})));
            //const jobsForTriggeringWorkflow = await getWorkflowJobsForRunId(octokit, repoInfo.owner, repoInfo.repo, triggeringWorkflowRunId);
            //core.info('TMP DEBUG jobsForTriggeringWorkflow=' + JSON.stringify(jobsForTriggeringWorkflow.map(v => {v.steps = '-_-'; return v;})));
            //core.info('TMP DEBUG job name=' + process.env.GITHUB_JOB);
            const commitSha = guessCommitSha();
            const startedAt = (new Date()).toISOString();
            const prNumber = guessPrNumber();
            const originalWorkflowName = guessTriggeringWorkflowName();
            const outputTitle = '🔔 ' + github.context.workflow; // Current workflow name !
            const originalWorkflowUrl = github.context.serverUrl + '/' + GITHUB_REPOSITORY + '/actions/runs/' + triggeringWorkflowRunId + (undefined !== prNumber ? '?pr=' + prNumber : '');
            const outputSummary = '🪢 Triggered by <a href="' + originalWorkflowUrl + '" target="blank">**' + originalWorkflowName + '** workflow</a>';

            return {
                name: checkName,
                head_sha: commitSha,
                //details_url: detailsUrl,
                external_id: triggeringWorkflowRunId?.toString(),
                status: isSuccessfulJobAsOfNow ? 'in_progress' : 'completed',
                output: {
                    title: outputTitle,
                    summary: outputSummary,
                },
                // Conclusion
                conclusion: isSuccessfulJobAsOfNow ? undefined : jobStatus,
                started_at: startedAt,
                completed_at: isSuccessfulJobAsOfNow ? undefined : startedAt,
                // Url path parameters
                owner: repoInfo.owner,
                repo: repoInfo.repo
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
    if (true === isSuccessfulJobAsOfNow) {
        core.saveState('check-run-already-concluded', 'yes'); // In order to use it during POST hook
    }
}

run();
