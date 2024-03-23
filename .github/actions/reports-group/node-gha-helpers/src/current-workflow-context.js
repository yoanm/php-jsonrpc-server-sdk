import {buildWorkflowRunUrl} from "./common";

const {context: ghaContext} = require('@actions/github');
const {payload: ghaEvent} = ghaContext;

const {isPullRequestEvent, isPushEvent} = require('./current-workflow-event');

/**
 * @type {GHAContextGetter}
 */
export const getContext = () => {
    const prNumber = getPrNumber();
    const runId = getRunId();

    return {
        repositoryOwner: ghaContext.repo.owner,
        repositoryName: ghaContext.repo.repo,
        commitSha: getCommitSha(),
        prNumber: prNumber,
        workflowName: getWorkflowName(),
        serverUrl: ghaContext.serverUrl,
        runId: runId,
        workflowRunUrl: buildWorkflowRunUrl(ghaContext.serverUrl, ghaContext.repo.owner + '/' + ghaContext.repo.repo, runId, prNumber),
    }
};

/**
 * @returns {string}
 */
export function getCommitSha() {
    if (isPullRequestEvent()) {
        return ghaEvent.pull_request.head.sha;
    }
    if (isPushEvent()) {
        return ghaEvent.after;
    }

    return ghaContext.sha;
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
