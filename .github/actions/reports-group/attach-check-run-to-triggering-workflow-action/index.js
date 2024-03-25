const core = require("@actions/core"); // @TODO move to 'imports from' when moved to TS !

if (!!core.getState('has-been-triggered')) {
    require('./src/cleanup');
} else {
    require('./src/main');
}
