const github = require('@actions/github'); // @TODO move to 'imports from' when moved to TS !
const core = require('@actions/core');

const {GITHUB_REPOSITORY, RUNNER_NAME} = process.env;

/**
 * @returns {number|undefined}
 */
function guessTriggeringPrNumber() {
    if ('pull_request' === github.context.eventName) {
        return github.context.payload.number;
    } else if ('workflow_run' === github.context.eventName  && 'pull_request' === github.context.payload.workflow_run.event) {
        return github.context.payload.workflow_run.pull_requests[0]?.number;
    }

    return undefined;
}

/**
 * @returns {string|undefined}
 */
function guessTriggeringCommitSha() {
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
function guessTriggeringRunId() {
    if ('workflow_run' === github.context.eventName) {
        return github.context.payload.workflow.id.toString();
    }

    return github.context.runId.toString();
}

/**
 * @returns {Promise<Record<string, any>|undefined>}
 */
async function retrieveCurrentJob(octokit, owner, repo, runId) {
    const jobList = await getWorkflowJobsForRunId(octokit, owner, repo, runId);
    core.info('TMP DEBUG jobsForCurrentWorkflow=' + JSON.stringify(jobList));
    const candidateList = [];
    for (const job of jobList) {
        if (RUNNER_NAME === job.runner_name && 'in_progress' === job.status) {
            candidateList.push(job);
        }
    }
    if (candidateList.length === 0) {
        core.info('Unable to retrieve the current job !');
        return undefined;
    }
    if (candidateList.length > 1) {
        core.warning(
            'Multiple running jobs rely on runners with the same name, unable to retrieve the current job !'
            + '\nCandidates: ' + Object.entries(candidateList).map(([k, v]) => v.name + '(' + k + ')').join(', ')
        );
        return undefined;
    }

    return candidateList.shift();
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
    const githubToken = core.getInput('github-token', {required: true});
    const jobStatus = core.getInput('job-status', {required: true});
    const checkName = core.getInput('name');

    const isSuccessfulJobAsOfNow = 'success' === jobStatus;
    const octokit = github.getOctokit(githubToken);

    const requestParams = await core.group(
        'Build API params',
        async () => {
            const repoInfo = github.context.repo;
            const triggeringWorkflowRunId = guessTriggeringRunId();
            core.info('TMP DEBUG context=' + JSON.stringify(github.context));
            //const jobsForCurrentWorkflow = await getWorkflowJobsForRunId(octokit, repoInfo.owner, repoInfo.repo, github.context.runId);
            //core.info('TMP DEBUG jobsForCurrentWorkflow=' + JSON.stringify(jobsForCurrentWorkflow));
            //const jobsForTriggeringWorkflow = await getWorkflowJobsForRunId(octokit, repoInfo.owner, repoInfo.repo, triggeringWorkflowRunId);
            //core.info('TMP DEBUG jobsForTriggeringWorkflow=' + JSON.stringify(jobsForTriggeringWorkflow));
            core.info('TMP DEBUG GITHUB_ACTION=' + process.env.GITHUB_ACTION);
            core.info('TMP DEBUG GITHUB_ACTION_PATH=' + process.env.GITHUB_ACTION_PATH);
            core.info('TMP DEBUG GITHUB_ACTION_REPOSITORY=' + process.env.GITHUB_ACTION_REPOSITORY);
            core.info('TMP DEBUG GITHUB_JOB=' + process.env.GITHUB_JOB);
            core.info('TMP DEBUG GITHUB_RUN_ATTEMPT=' + process.env.GITHUB_RUN_ATTEMPT);
            core.info('TMP DEBUG GITHUB_WORKFLOW=' + process.env.GITHUB_WORKFLOW);
            core.info('TMP DEBUG GITHUB_WORKFLOW_REF=' + process.env.GITHUB_WORKFLOW_REF);
            core.info('TMP DEBUG RUNNER_ARCH=' + process.env.RUNNER_ARCH);
            core.info('TMP DEBUG RUNNER_NAME=' + process.env.RUNNER_NAME);
            core.info('TMP DEBUG RUNNER_OS=' + process.env.RUNNER_OS);
            const currentJob = await retrieveCurrentJob(octokit, repoInfo.owner, repoInfo.repo, github.context.runId);
            core.info('TMP DEBUG CURRENT JOB=' + JSON.stringify(currentJob));
            const commitSha = guessTriggeringCommitSha();
            const startedAt = (new Date()).toISOString();
            const prNumber = guessTriggeringPrNumber();
            //const originalWorkflowName = guessTriggeringWorkflowName();
            const currentWorkflowName = github.context.workflow;
            const outputTitle = '🔔 ' + currentWorkflowName;
            const currentWorkflowUrl = github.context.serverUrl + '/' + GITHUB_REPOSITORY + '/actions/runs/' + github.context.runId.toString() + (undefined !== prNumber ? '?pr=' + prNumber : '');
            const outputSummary = '🪢 Check added by '
                + (currentJob ? '<a href="' + currentJob.html_url + '" target="blank">**' + currentJob.name + '**</a>' : '')
                + (currentJob ? ' (' : '') + '<a href="' + currentWorkflowUrl + '" target="blank">**' + currentWorkflowName + '** workflow</a>' + (currentJob ? ')' : '')
            ;

            return {
                name: checkName ? checkName : (currentJob?.name ?? currentWorkflowName + ' Check run'),
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
