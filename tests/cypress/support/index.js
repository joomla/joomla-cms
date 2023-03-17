import './commands';
import 'joomla-cypress';

const { registerCommands } = require('../../../node_modules/joomla-cypress/src/index.js');

before(() => {
  registerCommands();

  Cypress.on('uncaught:exception', (err, runnable) => {
    console.log(`err :${err}`);
    console.log(`runnable :${runnable}`);
    return false;
  });
});

afterEach(() => {
  cy.task('cleanupDB').then(() => {
    cy.task('queryDB', 'DELETE FROM #__user_usergroup_map WHERE user_id NOT IN (SELECT id FROM #__users)');
    cy.task('queryDB', 'DELETE FROM #__user_profiles WHERE user_id NOT IN (SELECT id FROM #__users)');
    return null;
  });
});
