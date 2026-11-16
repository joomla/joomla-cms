describe('Test accessibility on article list view', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
  });

  it('has no detectable a11y violations on article list view', () => {
    cy.visit('/administrator/index.php?option=com_content&view=articles');
    cy.contains('Filter Options').should('be.visible').click();
    cy.runAccessibilityScan('#j-main-container');
  });
});
