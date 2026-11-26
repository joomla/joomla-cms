import { logViolations } from '..';

Cypress.Commands.add('runAccessibilityScan', (theme = 'light', scope) => {
  const wcagStandards = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22a', 'wcag22aa'];
  const impactLevel = ['critical', 'minor', 'moderate', 'serious'];
  const continueOnFail = false;
  const checkA11yParams = theme === 'light'
    ? {
        runOnly: {
          type: 'tag',
          values: wcagStandards,
        },
        includedImpacts: impactLevel,
      }
    : {
        runOnly: {
          type: 'tag',
          // Test color contrasts in dark mode. No need to re-test everything else
          values: ['cat.color'],
        },
        includedImpacts: impactLevel,
        // Disable color-contrast-enhanced (AAA)
        rules: {
          'color-contrast-enhanced': { enabled: false },
        },
      };
  cy.injectAxe();
  cy.checkA11y(
    scope,
    checkA11yParams,
    logViolations,
    continueOnFail,
  );
});
