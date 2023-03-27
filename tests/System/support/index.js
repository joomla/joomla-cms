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

  // Turn off mailing
  cy.readFile(`${Cypress.env('cmsPath')}/configuration.php`)
    .then((content) => cy.task('writeFile', { path: 'configuration.php', content: content.replace(/^.*\$mailonline.*$/mg, 'public $mailonline = false;') }));
});

afterEach(() => {
  cy.checkForPhpNoticesOrWarnings();
  cy.task('cleanupDB');
});
