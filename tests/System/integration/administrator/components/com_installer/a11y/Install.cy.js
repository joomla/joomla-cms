describe('Test that installation views has no detectable a11y violations', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
  });

  it('in Install initial view', () => {
    cy.visit('/administrator/index.php?option=com_installer&view=install');
    cy.runAccessibilityScan('#content');
  });

  it('in Install view when Install from Folder is clicked', () => {
    cy.visit('/administrator/index.php?option=com_installer&view=install');
    cy.contains('[role="tab"]', 'Install from Folder').should('be.visible').click();
    cy.get('#folder').should('be.visible');
    cy.runAccessibilityScan('#folder');
  });

  it('in Install view when Install from URL is clicked', () => {
    cy.visit('/administrator/index.php?option=com_installer&view=install');
    cy.contains('[role="tab"]', 'Install from URL').should('be.visible').click();
    cy.get('#url').should('be.visible');
    cy.runAccessibilityScan('#url');
  });

  it('in Manage Extensions view', () => {
    cy.visit('/administrator/index.php?option=com_installer&view=manage');
    cy.contains('Filter Options').should('be.visible').click();
    cy.runAccessibilityScan('#content');
  });
});
