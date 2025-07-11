describe('Test in backend that the user form', () => {
  beforeEach(() => cy.doAdministratorLogin());
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
