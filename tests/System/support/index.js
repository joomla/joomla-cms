import('./commands.mjs');
import('joomla-cypress');

before(() => {
  cy.task('startMailServer');
});

afterEach(() => {
  cy.checkForPhpNoticesOrWarnings();
  cy.task('cleanupDB');
});
