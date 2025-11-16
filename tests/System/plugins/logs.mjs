import { existsSync, readFileSync, writeFileSync } from 'node:fs';

/**
 * Checks if there are entries written to the logs file.
 *
 * @param {object} config - The Cypress configuration object
 *
 * @returns null
 */
function checkForLogs(config) {
  const { logFile } = config.env;
  if (!existsSync(logFile)) {
    return null;
  }

  const log = readFileSync(logFile, 'utf8');
  if (!log) {
    return null;
  }

  throw new Error(log);
}

/**
 * Clears the log file.
 *
 * @param {object} config - The Cypress configuration object
 *
 * @returns null
 */
function clearLogs(config) {
  const { logFile } = config.env;
  if (!existsSync(logFile)) {
    return null;
  }

  writeFileSync(logFile, '');

  return null;
}

export { checkForLogs, clearLogs };
