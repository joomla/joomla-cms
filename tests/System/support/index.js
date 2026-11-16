import('./commands.mjs');
import('joomla-cypress');
import 'cypress-axe';

before(() => {
  cy.task('startMailServer');
  cy.task('clearLogs');
});

afterEach(() => {
  cy.checkForPhpNoticesOrWarnings();
  cy.task('checkForLogs');
  cy.task('cleanupDB');
});

// Log results
export function logViolations(violations) {
  cy.task(
    'log',
    `${violations.length} accessibility violation${
      violations.length === 1 ? '' : 's'
    } ${violations.length === 1 ? 'was' : 'were'} detected`,
  );

  const violationData = violations.map(
    ({ id, impact, description, nodes }) => ({
      id,
      impact,
      description,
      nodes: nodes.length,
    }),
  );

  cy.task('table', violationData);
}

// Disable "Transition was skipped" exceptions, which are happen randomly in installation test
Cypress.on('uncaught:exception', (err) => {
  if (err.message.includes('Transition was skipped')) {
    return false;
  }
});
