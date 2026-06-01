import('./commands.mjs');
import('joomla-cypress');

Cypress.on('window:before:load', (win) => {
  // Disable CSS-native cross-document view transitions
  const style = win.document.createElement('style');
  style.textContent = '* { view-transition-name: none !important; }';
  win.document.head?.appendChild(style);
});

before(() => {
  cy.task('startMailServer');
  cy.task('clearLogs');
});

afterEach(() => {
  cy.checkForPhpNoticesOrWarnings();
  cy.task('checkForLogs');
  cy.task('cleanupDB');
});
