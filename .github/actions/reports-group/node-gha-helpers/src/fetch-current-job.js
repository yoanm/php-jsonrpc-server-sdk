const {context: ghaContext} = require('@actions/github');
const core = require('@actions/core');

const {RUNNER_NAME} = process.env;

/**
 * @param {OctokitInterface} octokit
 *
 * @returns {Promise<WorkflowJob|undefined>}
 */
export async function fetchCurrentJob(octokit) {
    const jobList = await getWorkflowJobsForRunId(octokit, ghaContext.repo.owner, ghaContext.repo.repo, ghaContext.runId);
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

/**
 * @param {OctokitInterface} octokit
 * @param {string} owner
 * @param {string} repo
 * @param {string} runId
 *
 * @return {Promise<WorkflowJob[]>}
 */
async function getWorkflowJobsForRunId(octokit, owner, repo, runId) {
    return octokit.paginate(
        'GET /repos/{owner}/{repo}/actions/runs/{run_id}/jobs',
        {
            filter: 'latest',
            // Url path parameters
            owner: owner,
            repo: repo,
            run_id: runId
        }
    );
}
