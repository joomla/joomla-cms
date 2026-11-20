describe('Test in backend that the user list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_users&view=users&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Users');
  });

  it('can display a list of users', () => {
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
      cy.clickDialogConfirm(true);

      cy.checkForSystemMessage('User deleted.');
    });
  });

  it('can filter state', () => {
    cy.db_createUser({ name: 'Test user 1', username: 'test1', block: 0 })
      .then(() => cy.db_createUser({ name: 'Test user 2', username: 'test2', block: 1 }))
      .then(() => {
        cy.reload();

        cy.get('#userList')
          .should('contain', 'Test user 1')
          .should('contain', 'Test user 2');

        cy.setFilter('state', 'Enabled');

        cy.get('#userList')
          .should('contain', 'Test user 1')
          .should('not.contain', 'Test user 2');

        cy.setFilter('state', 'Disabled');

        cy.get('#userList')
          .should('not.contain', 'Test user 1')
          .should('contain', 'Test user 2');
      });
  });

  it('can filter group', () => {
    cy.db_createUser({ name: 'Test user 1', username: 'test1', group_id: 2 })
      .then(() => cy.db_createUser({ name: 'Test user 2', username: 'test2', group_id: 6 }))
      .then(() => {
        cy.reload();

        cy.get('#userList')
          .should('contain', 'Test user 1')
          .should('contain', 'Test user 2');

        cy.setFilter('group_id', '- Registered');

        cy.get('#userList')
          .should('contain', 'Test user 1')
          .should('not.contain', 'Test user 2');

        cy.setFilter('group_id', '- Manager');

        cy.get('#userList')
          .should('not.contain', 'Test user 1')
          .should('contain', 'Test user 2');

        cy.setFilter('group_id', '- Super Users');

        cy.get('#userList')
          .should('not.contain', 'Test user 1')
          .should('not.contain', 'Test user 2');
      });
  });
});
