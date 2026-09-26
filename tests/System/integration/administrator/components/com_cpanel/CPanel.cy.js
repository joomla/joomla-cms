describe('Test in backend that the cpanel system dashboard', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_cpanel&view=cpanel&dashboard=system');
  });

  it('has a title', () => {
    cy.contains('h1', 'System Dashboard').should('exist');
  });

  it('can display the modules', () => {
    cy.contains('h2', 'Setup').should('exist');
    cy.contains('h2', 'Install').should('exist');
    cy.contains('h2', 'Templates').should('exist');
    cy.contains('h2', 'Maintenance').should('exist');
    cy.contains('h2', 'Manage').should('exist');
    cy.contains('h2', 'Information').should('exist');
    cy.contains('h2', 'Update').should('exist');
    cy.contains('h2', 'User Permissions ').should('exist');
  });
});
