const {context: ghaContext} = require('@actions/github');
const {payload: ghaEvent} = ghaContext;

const {isPullRequestEvent, isPushEvent} = require('./workflow-run-event');

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
 * @returns {number|undefined}
 */
export const getPrNumber = () => isPullRequestEvent() ? ghaEvent.workflow_run.pull_requests[0]?.number : undefined;

/**
 * @returns {string|undefined}
 */
export const getCommitSha = () => isPullRequestEvent() || isPushEvent()  ? ghaEvent.workflow_run.head_sha : undefined;

/**
 * @returns {string}
 */
export const getWorkflowName = () => ghaEvent.workflow.name;

/**
 * @returns {string}
 */
export const getRunId = () => ghaEvent.workflow.id.toString();
