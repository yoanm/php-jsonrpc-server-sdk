/**
 * @param {string} serverUrl
 * @param {string} repositorySlug
 * @param {string} runId
 * @param {string|number|undefined|null} prNumber
 *
 * @return {string}
 */
export function buildWorkflowRunUrl(serverUrl, repositorySlug, runId, prNumber = undefined) {
    return serverUrl + '/' + repositorySlug + '/actions/runs/' + runId + (!prNumber ? '' : '?pr=' + prNumber);
}

/**
 *
 * @param {{html_url: string}} job
 * @param {string|number|undefined|null} prNumber
 *
 * @return {string}
 */
export function buildWorkflowJobRunUrl(job, prNumber = undefined) {
    return job.html_url + (!prNumber ? '' : '?pr=' + prNumber)
}
