import { getMails, clearEmails, startMailServer } from "./mail.mjs";
import { writeFile, deleteFolder } from "./fs.mjs";
import { queryTestDB, deleteInsertedItems } from "./db.mjs";

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
    writeFile: ({ path, content }) => writeFile(path, content, config),
    deleteFolder: (path) => deleteFolder(path, config),
    getMails: () => getMails(),
    clearEmails: () => clearEmails(),
    startMailServer: () => startMailServer(config),
  });
}
