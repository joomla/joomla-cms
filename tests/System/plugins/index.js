const mail = require('./mail');
const fs = require('./fs');
const db = require('./db');

/**
 * Does the setup of the plugins.
 *
 * @param {*} on
 * @param {object} config The configuration
 *
 * @see https://docs.cypress.io/guides/references/configuration#setupNodeEvents
 */
function setupPlugins(on, config) {
  on('task', {
    queryDB: (query) => db.queryTestDB(query, config),
    cleanupDB: () => db.deleteInsertedItems(config),
    writeRelativeFile: ({ path, content, mode }) => fs.writeRelativeFile(path, content, config, mode),
    deleteRelativePath: (path) => fs.deleteRelativePath(path, config),
    getMails: () => mail.getMails(),
    clearEmails: () => mail.clearEmails(),
    startMailServer: () => mail.startMailServer(config),
  });
}

module.exports = setupPlugins;
