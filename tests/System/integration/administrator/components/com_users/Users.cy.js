describe('Test that the user back end list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('administrator/index.php?option=com_users&view=users&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Users');
  });

  it('can show a list of users', () => {
    cy.db_createUser({ name: 'Test user' }).then(() => {
      cy.reload();

      cy.contains('Test user');
    });
  });

  it('can open the user form', () => {
    cy.clickToolbarButton('New');

    cy.contains('New User Details');
  });

  it('can delete the test user', () => {
    cy.db_createUser({ name: 'Test user' }).then(() => {
      cy.searchForItem('Test user');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Delete').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('User deleted.').should('exist');
    });
  });
});
