describe('Test in backend that the user form', () => {
  beforeEach(() => cy.doAdministratorLogin(undefined, undefined, false));
  afterEach(() => cy.task('queryDB', "DELETE FROM #__users WHERE username = 'test'"));

  it('can create a new user', () => {
    cy.visit('/administrator/index.php?option=com_users&task=user.add');

    cy.get('#jform_name').clear().type('test user');
    cy.get('#jform_username').clear().type('test');
    cy.get('#jform_email').clear().type('test@example.com');
    cy.get('#jform_password').clear().type('testtesttest');
    cy.get('#jform_password2').clear().type('testtesttest');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('User saved');
    cy.contains('test user');
  });

  it('can edit a user', () => {
    cy.db_createUser().then((id) => {
      cy.visit(`/administrator/index.php?option=com_users&task=user.edit&id=${id}`);

      cy.get('#jform_name').clear().type('test edited');
      cy.get('#jform_username').clear().type('testedited');
      cy.get('#jform_password').clear().type('testeditedtest');
      cy.get('#jform_password2').clear().type('testeditedtest');
      cy.get('#jform_email').clear().type('testedited@example.com');
      cy.clickToolbarButton('Save');

      cy.checkForSystemMessage('User saved.');
    });
  });
  it('can create a new user from the selected group filter', () => {
    cy.visit('/administrator/index.php?option=com_users&view=users');

    cy.setFilter('group_id', '- Manager');

    cy.clickToolbarButton('New');

    cy.get('input[name="jform[groups][]"][value="6"]')
      .should('be.checked');

    // Return to the users list and reset the filter
    cy.visit('/administrator/index.php?option=com_users&view=users');
    cy.setFilter('group_id', '');
  });
  it('can create a new user with the selected group using Save & New', () => {
    cy.visit('/administrator/index.php?option=com_users&view=users');

    cy.setFilter('group_id', '- Manager');

    cy.clickToolbarButton('New');

    cy.get('input[name="jform[groups][]"][value="6"]')
      .should('be.checked');

    cy.get('#jform_name').clear().type('test user');
    cy.get('#jform_username').clear().type('test');
    cy.get('#jform_email').clear().type('test@example.com');
    cy.get('#jform_password').clear().type('testtesttest');
    cy.get('#jform_password2').clear().type('testtesttest');

    cy.get('#toolbar-dropdown-save-group .btn.btn-success.dropdown-toggle-split').click();
    cy.clickToolbarButton('Save & New');

    cy.checkForSystemMessage('User saved');

    // The new user form should retain the selected group.
    cy.get('input[name="jform[groups][]"][value="6"]')
      .should('be.checked');

    // Reset the filter.
    cy.visit('/administrator/index.php?option=com_users&view=users');
    cy.setFilter('group_id', '');
  });
  it('can create a new user from the Add User quick task link on the menu with the selected group', () => {
    cy.visit('/administrator/index.php?option=com_users&view=users');

    cy.setFilter('group_id', '- Manager');

    // Click the Add User quick-task link in the Users submenu.
    cy.get('.menu-quicktask a[href*="task=user.add"]').click();

    cy.get('input[name="jform[groups][]"][value="6"]')
      .should('be.checked');

    // Reset the filter.
    cy.visit('/administrator/index.php?option=com_users&view=users');
    cy.setFilter('group_id', '');
  });
  it('selects Registered when an Administrator filters by Super Users and creates a new user', () => {
    cy.db_createUser({
      name: 'Test Administrator',
      username: 'test',
      email: 'testadmin@example.com',
      password: '098f6bcd4621d373cade4e832627b4f6',
      group_id: 7,
    }).then(() => {
      cy.doAdministratorLogout();
      cy.doAdministratorLogin('test', 'test', false);

      cy.contains('.shepherd-button-secondary', 'Hide Forever')
        .should('be.visible')
        .click();

      cy.visit('/administrator/index.php?option=com_users&view=users');

      cy.setFilter('group_id', '- Super Users');

      cy.clickToolbarButton('New');

      // Registered (group ID 2) should be selected.
      cy.get('input[name="jform[groups][]"][value="2"]')
        .should('be.checked');

      cy.doAdministratorLogout();
    });
  });
  it('can reset password for a user', () => {
    cy.db_createUser({
      name: 'automated test user',
      username: 'test',
      email: 'test@example.com',
      password: '098f6bcd4621d373cade4e832627b4f6',
      group_id: 8,
      requireReset: 1,
    }).then(() => {
      // Check that the user is required to reset the password
      cy.visit('/administrator/index.php?option=com_users');
      cy.contains('Password Reset Required').should('exist');
      cy.doAdministratorLogout();

      // Check that the user is redirected to the password reset page
      cy.visit('administrator/index.php');
      cy.get('#mod-login-username').type('test');
      cy.get('#mod-login-password').type('test');
      cy.get('#btn-login-submit').click();
      cy.contains('You are required to reset your password before proceeding.').should('exist');
      cy.get('#jform_password').clear().type('testresetpswd');
      cy.get('#jform_password2').clear().type('testresetpswd');
      cy.clickToolbarButton('Save & Close');
      cy.checkForSystemMessage('User saved.');
      cy.doAdministratorLogout();

      // Check that the user can login with the new password
      cy.visit('administrator/index.php');
      cy.get('#mod-login-username').type('test');
      cy.get('#mod-login-password').type('testresetpswd');
      cy.get('#btn-login-submit').click();

      cy.visit('/administrator/index.php?option=com_users');
      cy.contains('Password Reset Required').should('not.exist');
    });
  });
});
