import('./commands.mjs');
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

// Disable "Transition was skipped" exceptions, which are happen randomly in installation test
Cypress.on('uncaught:exception', (err) => {
  if (err.message.includes('Transition was skipped')) {
    return false;
  }
});
