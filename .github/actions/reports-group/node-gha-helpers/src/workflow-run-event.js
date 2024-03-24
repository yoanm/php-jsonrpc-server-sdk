const {context: ghaContext} = require('@actions/github');
/** @var {{workflow_run?: Record<string, any>}} ghaEvent */
const {payload: ghaEvent} = ghaContext;

/**
 * @returns {boolean}
 */
export const isWorkflowRunEvent = () => ghaEvent.workflow_run && 'workflow_run' === ghaEvent.workflow_run.event;

/**
 * @returns {boolean}
 */
export const isPullRequestEvent = () => ghaEvent.workflow_run && 'pull_request' === ghaEvent.workflow_run.event;

/**
 * @returns {boolean}
 */
export const isPushEvent = () => ghaEvent.workflow_run && 'push' === ghaEvent.workflow_run.event;
