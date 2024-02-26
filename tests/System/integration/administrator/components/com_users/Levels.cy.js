describe('Test in backend that the user access level list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_users&view=levels&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Users: Viewing Access Level');
  });

  it('can display a list of access levels', () => {
    cy.db_createUserLevel({ title: 'Test level' }).then(() => {
      cy.reload();

      cy.contains('Test level');
    });
  });

  it('can open the access level form', () => {
    cy.clickToolbarButton('New');

    cy.contains('Level Details');
  });

  it('can delete the test level', () => {
    cy.db_createUserLevel({ title: 'Test level' }).then(() => {
      cy.searchForItem('Test level');
      cy.checkAllResults();
      cy.clickToolbarButton('Delete');
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('View Access Level removed.').should('exist');
    });
  });
});
