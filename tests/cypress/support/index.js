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

afterEach(() => cy.task('cleanupDB'));
