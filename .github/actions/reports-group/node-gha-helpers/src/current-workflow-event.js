const {context: ghaContext} = require('@actions/github');

/**
 * @returns {boolean}
 */
export const isWorkflowRunEvent = () => 'workflow_run' === ghaContext.eventName;

/**
 * @returns {boolean}
 */
export const isPullRequestEvent = () => 'pull_request' === ghaContext.eventName;

/**
 * @returns {boolean}
 */
export const isPushEvent = () => 'push' === ghaContext.eventName;
