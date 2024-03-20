const core = require('@actions/core'); // @TODO move to 'imports from' when moved to TS !

export function bindFrom(outputs) {
    Object.entries(outputs).map(([outputName, outputValue]) => {
        core.debug('Output ' + outputName + '=' +outputValue);
        core.setOutput(outputName, outputValue);
    });
}
