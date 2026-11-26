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

  describe('custom-field filter', () => {
    let fieldId;

    beforeEach(() => {
      fieldId = undefined;
    });

    afterEach(() => {
      if (fieldId) {
        return cy.task('queryDB', `DELETE FROM #__fields_values WHERE field_id = ${fieldId}`);
      }

      return undefined;
    });

    it('filters, remembers, and clears an opted-in user field', () => {
      cy.db_createField({
        title: 'Users list filter',
        name: 'users-list-filter',
        label: 'Users list filter',
        context: 'com_users.user',
        type: 'list',
        required: 0,
        params: JSON.stringify({ show_in_admin_list_filter: 1 }),
        fieldparams: JSON.stringify({ options: { options0: { name: 'Matched', value: 'matched' } } }),
      }).then((id) => {
        fieldId = id;
        return cy.db_createUser({ name: 'User filter matched', username: 'filter-matched' });
      }).then((item) => cy.db_createFieldValue({ field_id: fieldId, item_id: item, value: 'matched' }))
        .then(() => cy.db_createUser({ name: 'User filter unmatched', username: 'filter-unmatched' }))
        .then(() => {
          cy.reload();
          cy.get(`#filter_customfield_${fieldId}`).should('not.be.disabled')
            .select('matched', { force: true });
          cy.get('#userList').should('contain.text', 'User filter matched')
            .and('not.contain.text', 'User filter unmatched');

          cy.reload();
          cy.get(`#filter_customfield_${fieldId} option:selected`).should('have.value', 'matched');
          cy.get('#userList').should('contain.text', 'User filter matched')
            .and('not.contain.text', 'User filter unmatched');

          cy.get('.js-stools-btn-clear').click();
          cy.get('#userList').should('contain.text', 'User filter matched')
            .and('contain.text', 'User filter unmatched');
        });
    });
  });
});
