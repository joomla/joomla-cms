import { execFileSync } from 'node:child_process';

/**
 * Executes the CLI application and returns the command result
 *
 * @param {object} [options={}] - The execution options
 * @param {string[]} [options.args=[]] - Command arguments to pass to the CLI
 * @param {boolean} [options.failOnNonZeroExit=true] - Whether to throw an error on non-zero exit codes
 * @param {object} config - The Cypress configuration object
 *
 * @returns {object} - An object containing the command output, error output and exit code
 * @returns {number} returns.exitCode - The exit code of the CLI command
 * @returns {string} returns.stdout - The standard output of the command
 * @returns {string|null} returns.stderr - The standard error output of the command, or null on success
 *
 * @throws {Error} - If failOnNonZeroExit is true and the command exits with a non-zero code
 */
function executeCli(options = {}, config) {
  const {
    args = [],
    failOnNonZeroExit = true,
  } = options;

  try {
    const output = execFileSync(
      'php',
      [`${config.expose.cmsPath}/cli/joomla.php`, ...args, '--no-ansi'],
      { encoding: 'utf8' },
    );

    return {
      exitCode: 0,
      stdout: output,
      stderr: null,
    };
  } catch (error) {
    if (failOnNonZeroExit) {
      throw new Error(`CLI command failed with exit code ${error.status}\n${error.stderr.trim()}\n${error.stdout.trim()}`);
    }

    return {
      exitCode: error.status,
      stdout: error.stdout,
      stderr: error.stderr,
    };
  }
}

export { executeCli };
