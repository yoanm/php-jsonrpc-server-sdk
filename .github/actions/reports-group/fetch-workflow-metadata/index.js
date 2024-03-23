import * as SDK from '../node-gha-helpers';

async function run() {
    const context = core.getBooleanInput('triggering-workflow', {required: true}) ? SDK.triggeringWorkflow.getContext() : SDK.getContext();

    core.setOutput('commit-sha', context.commitSha ?? null);
    core.setOutput('pull-request', context.prNumber ?? null);
    core.setOutput('workflow-name', context.workflowName);
    core.setOutput('run-id', context.runId);
}

run();
