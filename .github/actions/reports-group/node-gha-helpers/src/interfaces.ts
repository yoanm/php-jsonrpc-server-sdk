export type GHAContext = {
    repositoryOwner: string;
    repositoryName: string;
    commitSha: string;
    branch: string;
    prNumber: (number|undefined);
    isPrFromFork: boolean;
    workflowName: string;
    runId: string;
    workflowRunUrl: string;
    serverUrl: string;
}

export type GHAContextGetter = () => GHAContext;

export type ContextHelperSet = {
    getContext: GHAContextGetter;
    eventHelpers: GHAEventHelpers;
    contextHelpers: GHAContextHelpers;
}

import { Octokit } from '@octokit/core';
export type OctokitInterface = typeof Octokit & import("@octokit/core/dist-types/types").Constructor<import("@octokit/plugin-rest-endpoint-methods/dist-types/types").Api & {
    paginate: import("@octokit/plugin-paginate-rest").PaginateInterface;
}>;

export interface GHAContextHelpers {
    getContext: GHAContextGetter;
    getCommitSha: () => string;
    getPrNumber: () => number|undefined;
    getWorkflowName: () => string;
    getRunId: () => string;
}

export interface GHAEventHelpers {
    isWorkflowRunEvent: () => boolean;
    isPullRequestEvent: () => boolean;
    isPushEvent: () => boolean;
}

export type WorkflowJobStep =
    | WorkflowJobStepInProgress
    | WorkflowJobStepQueued
    | WorkflowJobStepCompleted

/**
 * The workflow job. Many `workflow_job` keys, such as `head_sha`, `conclusion`, and `started_at` are the same as those in a [`check_run`](#check_run) object.
 */
export interface WorkflowJob {
    id: number
    run_id: number
    run_attempt: number
    run_url: string
    head_sha: string
    node_id: string
    name: string
    check_run_url: string
    html_url: string
    url: string
    /**
     * The current status of the job. Can be `queued`, `in_progress`, or `completed`.
     */
    status: "queued" | "in_progress" | "completed" | "waiting"
    steps: WorkflowJobStep[]
    conclusion: "success" | "failure" | "cancelled" | "skipped" | null
    /**
     * Custom labels for the job. Specified by the [`"runs-on"` attribute](https://docs.github.com/en/actions/reference/workflow-syntax-for-github-actions#jobsjob_idruns-on) in the workflow YAML.
     */
    labels: string[]
    /**
     * The ID of the runner that is running this job. This will be `null` as long as `workflow_job[status]` is `queued`.
     */
    runner_id: number | null
    /**
     * The name of the runner that is running this job. This will be `null` as long as `workflow_job[status]` is `queued`.
     */
    runner_name: string | null
    /**
     * The ID of the runner group that is running this job. This will be `null` as long as `workflow_job[status]` is `queued`.
     */
    runner_group_id: number | null
    /**
     * The name of the runner group that is running this job. This will be `null` as long as `workflow_job[status]` is `queued`.
     */
    runner_group_name: string | null
    started_at: string
    completed_at: string | null
    /**
     * The name of the workflow.
     */
    workflow_name: string | null
    /**
     * The name of the current branch.
     */
    head_branch: string | null
    created_at: string
}
export interface WorkflowJobStepInProgress {
    name: string
    status: "in_progress"
    conclusion: null
    number: number
    started_at: string
    completed_at: null
}
export interface WorkflowJobStepQueued {
    name: string
    status: "queued"
    conclusion: null
    number: number
    started_at: null
    completed_at: null
}
export interface WorkflowJobStepCompleted {
    name: string
    status: "completed"
    conclusion: "failure" | "skipped" | "success" | "cancelled"
    number: number
    started_at: string
    completed_at: string
}
