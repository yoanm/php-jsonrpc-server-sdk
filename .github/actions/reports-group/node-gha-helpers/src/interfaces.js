/**
 * @typedef {{repositoryOwner: string, repositoryName: string, commitSha: string|undefined, prNumber: number|undefined, workflowName: string, serverUrl: string, runId: string}} GHAContext
 */
/**
 * @typedef {() => GHAContext} GHAContextGetter
 */
/**
 * @typedef {{getContext: GHAContextGetter, getCommitSha: () => string|undefined, getPrNumber: () => number|undefined, getWorkflowName: () => string, run: () => string}} GHAContextHelpers
 */
/**
 * @typedef {{isWorkflowRunEvent: () => boolean, isPullRequestEvent: () => boolean, isPushEvent: () => boolean}} GHAEventHelpers
 */
