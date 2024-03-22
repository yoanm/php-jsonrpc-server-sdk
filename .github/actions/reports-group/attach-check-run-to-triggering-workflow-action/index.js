const core = require("@actions/core"); // @TODO move to 'imports from' when moved to TS !

if (!!core.getState('check-run-id')) {
    require('./src/cleanup');
} else {
    require('./src/main');
}
