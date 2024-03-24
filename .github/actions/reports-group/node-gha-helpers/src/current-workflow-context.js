/** @var {{[key: string]: any, repo:  {owner: string, repo: string}, serverUrl: string}} ghaContext */
const {context: ghaContext} = require('@actions/github');
/** @var {{[key: string]: any, repository?:  Record<string, any>, pull_request?: Record<string, any>}} ghaEvent */
const {payload: ghaEvent} = ghaContext;

const {isPullRequestEvent, isPushEvent} = require('./current-workflow-event');
import {buildWorkflowRunUrl} from "./common";

/**
 * @type {GHAContext}
 */
export function getContext() {
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
    };
}

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
export function getPrNumber() {
    return isPullRequestEvent() ? ghaEvent.number : undefined;
}

/**
 * @returns {string}
 */
export function getWorkflowName() {
    return ghaContext.workflow;
}

/**
 * @returns {string}
 */
export function getRunId() {
    return ghaContext.runId.toString();
}

/**
 * @return {string}
 */
export function getBranch() {
    if (isPullRequestEvent()) {
        return ghaEvent.pull_request.head.ref;
    }

    // In case ref is not a branch (e.g. a tag), fallback to repository default branch
    return ghaContext.ref.startsWith('refs/heads') ? ghaContext.ref.replace('refs/heads/', '') : ghaEvent.repository.default_branch;
}

/**
 * @return {boolean}
 */
export function isPRFromFork() {
    return isPullRequestEvent() && ghaEvent.pull_request.head.repo.id === ghaEvent.pull_request.base.repo.id
}
