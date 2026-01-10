import('./commands.mjs');
import('./constants.mjs');
import('joomla-cypress');

before(() => {
  cy.task('startMailServer');
  cy.task('clearLogs');
});

afterEach(() => {
  cy.checkForPhpNoticesOrWarnings();
  cy.task('checkForLogs');
  cy.task('cleanupDB');
});
