import { getMails, clearEmails, startMailServer } from './mail.mjs';
import { writeRelativeFile, deleteRelativePath, copyRelativeFile } from './fs.mjs';
import { queryTestDB, deleteInsertedItems } from './db.mjs';

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
    getMails: () => getMails(),
    clearEmails: () => clearEmails(),
    startMailServer: () => startMailServer(config),
  });
}
