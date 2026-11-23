import { logViolations } from '..';

/**
 * Checks if accessibility violations should fail the test through `--expose a11yFailOnViolation=true`.
 * By default violations are only logged and collected in tests/System/output/a11y-violations.yml.
 *
 * @returns {boolean}
 */
function failOnViolation() {
  return [true, 'true', 1, '1'].includes(Cypress.expose('a11yFailOnViolation'));
}

Cypress.Commands.add('runAccessibilityScan', (theme = 'light', scope) => {
  const wcagStandards = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22a', 'wcag22aa'];
  const impactLevel = ['critical', 'minor', 'moderate', 'serious'];
  const continueOnFail = !failOnViolation();
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
    (violations) => {
      logViolations(violations);
      cy.task('recordA11yViolations', {
        spec: Cypress.spec.relative,
        test: Cypress.currentTest.titlePath.join(' > '),
        attempt: Cypress.currentRetry,
        violations: violations.map(({
          id, impact, description, help, helpUrl, nodes,
        }) => ({
          id,
          impact,
          description,
          help,
          helpUrl,
          nodes: nodes.map(({ target, html, failureSummary }) => ({ target, html, failureSummary })),
        })),
      }, { log: false });
    },
    continueOnFail,
  );
});
