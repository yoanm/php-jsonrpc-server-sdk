const {context: ghaContext} = require('@actions/github');
const {payload: ghaEvent} = ghaContext;

const {isPullRequestEvent} = require('./workflow-run-event');
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
        prNumber: prNumber,
        workflowName: getWorkflowName(),
        serverUrl: ghaContext.serverUrl,
        runId: runId,
        workflowRunUrl: buildWorkflowRunUrl(ghaContext.serverUrl, ghaContext.repo.owner + '/' + ghaContext.repo.repo, runId, prNumber),
    }
};

/**
 * @returns {number|undefined}
 */
export const getPrNumber = () => isPullRequestEvent() ? ghaEvent.workflow_run.pull_requests[0]?.number : undefined;

/**
 * @returns {string}
 */
export const getCommitSha = () => ghaEvent.workflow_run.head_sha;

/**
 * @returns {string}
 */
export const getWorkflowName = () => ghaEvent.workflow.name;

/**
 * @returns {string}
 */
export const getRunId = () => ghaEvent.workflow.id.toString();
