/**
 * @typedef {{repositoryOwner: string, repositoryName: string, commitSha: string, prNumber: number|undefined, workflowName: string, runId: string, workflowRunUrl: string}} GHAContext
 */
/**
 * @typedef {() => GHAContext} GHAContextGetter
 */
/**
 * @typedef {{getContext: GHAContextGetter, getCommitSha: () => string, getPrNumber: () => number|undefined, getWorkflowName: () => string, getRunId: () => string}} GHAContextHelpers
 */
/**
 * @typedef {{isWorkflowRunEvent: () => boolean, isPullRequestEvent: () => boolean, isPushEvent: () => boolean}} GHAEventHelpers
 */
