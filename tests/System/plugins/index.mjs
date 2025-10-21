import { deleteInsertedItems, queryTestDB } from './db.mjs';
import { copyRelativeFile, deleteRelativePath, writeRelativeFile } from './fs.mjs';
import { checkForLogs, clearLogs } from './logs.mjs';
import { clearEmails, getMails, startMailServer } from './mail.mjs';

/**
 * Does the setup of the plugins.
 *
 * @param {*} on
 * @param {object} config The configuration
 *
 * @see https://docs.cypress.io/guides/references/configuration#setupNodeEvents
 */
export default function setupPlugins(on, config) {
  on('task', {
    queryDB: (query) => queryTestDB(query, config),
    cleanupDB: () => deleteInsertedItems(config),
    writeRelativeFile: ({ path, content, mode }) => writeRelativeFile(path, content, config, mode),
    deleteRelativePath: (path) => deleteRelativePath(path, config),
    copyRelativeFile: ({ source, destination }) => copyRelativeFile(source, destination, config),
    checkForLogs: () => checkForLogs(config),
    clearLogs: () => clearLogs(config),
    getMails: () => getMails(),
    clearEmails: () => clearEmails(),
    startMailServer: () => startMailServer(config),
  });
}
