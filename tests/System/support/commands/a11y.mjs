import { logViolations } from "..";

Cypress.Commands.add("runAccessibilityScan", (scope) => {
  const wcagStandards = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22a', 'wcag22aa'];
  const impactLevel = ['critical', 'minor', 'moderate', 'serious'];
  const continueOnFail = false;
  cy.injectAxe();
  cy.checkA11y(
      scope, 
      {
          runOnly: {
              type: "tag",
              values: wcagStandards,
          },
          includedImpacts: impactLevel,
      },
      logViolations,
      continueOnFail
  );
});
