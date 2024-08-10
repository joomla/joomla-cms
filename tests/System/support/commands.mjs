/**
 * Imports commands from files. The commands start with the folder name and an underscore as Cypress doesn't support
 * namespaces for commands.
 *
 * https://github.com/cypress-io/cypress/issues/6575
 */

import { registerCommands } from 'joomla-cypress';

import './commands/api.mjs';
import './commands/config.mjs';
import './commands/db.mjs';

registerCommands();
