const {context: ghaContext} = require('@actions/github');
const {payload: ghaEvent} = ghaContext;

const {isPullRequestEvent, isPushEvent} = require('./current-workflow-event');
import {buildWorkflowRunUrl} from "./common";

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
        branch: getBranch(),
        prNumber: prNumber,
        isPrFromFork: isPRFromFork(),
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

/**
 * @return {string}
 */
export const getBranch = () => {
    if (isPullRequestEvent()) {
        return ghaEvent.pull_request.head.ref;
    }

    // In case ref is not a branch (e.g. a tag), fallback to repository default branch
    return ghaContext.ref.startsWith('refs/heads') ? ghaContext.ref.replace('refs/heads/', '') : ghaEvent.repository.default_branch;
};

/**
 * @return {boolean}
 */
export const isPRFromFork = () => isPullRequestEvent() && ghaEvent.pull_request.head.repo.id === ghaEvent.pull_request.base.repo.id;
