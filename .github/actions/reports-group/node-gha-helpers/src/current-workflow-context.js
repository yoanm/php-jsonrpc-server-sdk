const {context: ghaContext} = require('@actions/github');
const {payload: ghaEvent} = ghaContext;

const {isPullRequestEvent, isPushEvent} = require('./current-workflow-event');

/**
 * @returns {GHAContext}
 */
export const getContext = () => ({
    repositoryOwner: ghaContext.repo.owner,
    repositoryName: ghaContext.repo.repo,
    commitSha: getCommitSha(),
    prNumber: getPrNumber(),
    workflowName: getWorkflowName(),
    serverUrl: ghaContext.serverUrl,
    runId: getRunId(),
});

/**
 * @returns {string|undefined}
 */
export function getCommitSha() {
    if (isPullRequestEvent()) {
        return ghaEvent.pull_request.head.sha;
    }
    if (isPushEvent()) {
        return ghaEvent.after;
    }

    return undefined;
}

/**
 * @returns {number|undefined}
 */
export const getPrNumber = () => isPullRequestEvent() ? ghaEvent.number : undefined;

/**
 * @returns {string}
 */
export const getWorkflowName = () => ghaContext.workflow;

/**
 * @returns {string}
 */
export const getRunId = () => ghaContext.runId.toString();
