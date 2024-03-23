/** @type {GHAEventHelpers} */
import * as currentWorkflowEventHelpers from './src/current-workflow-event';
/** @type {GHAContextHelpers} */
import * as currentWorkflowContextHelpers from './src/current-workflow-context';
/** @type {GHAEventHelpers} */
import * as workflowRunEventHelpers from './src/workflow-run-event';
/** @type {GHAContextHelpers} */
import * as workflowRunContextHelpers from './src/workflow-run-context';

const isWorkflowRunEvent = currentWorkflowEventHelpers.isWorkflowRunEvent();

/**
 * @function
 * @return {GHAContextGetter}
 */
export const getContext = currentWorkflowContextHelpers.getContext;
/**
 * @type {GHAEventHelpers}
 */
export const eventHelpers = currentWorkflowEventHelpers;
/**
 * @type {GHAContextHelpers}
 */
export const contextHelpers = currentWorkflowContextHelpers;

/**
 * @type {{getContext: GHAContextGetter, eventHelpers: GHAEventHelpers, contextHelpers: GHAContextHelpers}}
 */
export const triggeringWorkflow = {
    getContext: isWorkflowRunEvent ? workflowRunContextHelpers.getContext : currentWorkflowContextHelpers.getContext,
    eventHelpers: isWorkflowRunEvent ? workflowRunEventHelpers : currentWorkflowEventHelpers,
    contextHelpers: isWorkflowRunEvent ? workflowRunContextHelpers : currentWorkflowContextHelpers,
}

export * from './src/fetch-current-job';
