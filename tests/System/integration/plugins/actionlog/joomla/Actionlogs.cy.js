describe('Action Logs - User Event Scenarios', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
  });

  afterEach(() => {
    // Clean up created test user to keep tests stateless
    cy.task('queryDB', "DELETE FROM #__users WHERE username = 'test'");
    cy.task('queryDB', 'TRUNCATE #__action_logs');
  });

  it('logs user creation (backend)', () => {
    // Navigate to Users and create a new user
    cy.visit('/administrator/index.php?option=com_users&task=user.add');

    cy.get('#jform_name').clear().type('test user');
    cy.get('#jform_username').clear().type('test');
    cy.get('#jform_email').clear().type('test@example.com');
    cy.get('#jform_password').clear().type('testtesttest');
    cy.get('#jform_password2').clear().type('testtesttest');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('User saved');
    cy.contains('test user');

    // Verify Action Log entry
    cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
    cy.contains('added new user').should('be.visible');
  });

  it('logs user modifications (backend)', () => {
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

    // Verify Action Log records the update
    cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
    cy.contains('updated the user').should('be.visible');
  });

  it('logs blocking and unblocking via user profile toggle (backend)', () => {
    cy.db_createUser().then((id) => {
      // Open the user profile edit view directly using the returned ID
      cy.visit(`/administrator/index.php?option=com_users&task=user.edit&id=${id}`);

      // Block the User: Click the "Blocked" label (which targets input id="jform_block0")
      cy.get('label[for="jform_block0"]').click();
      cy.clickToolbarButton('Save');
      cy.checkForSystemMessage('User saved.');

      // Assert that the Block action log entry exists
      cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
      cy.contains('blocked user').should('be.visible');

      // Go back to the user profile to unblock them
      cy.visit(`/administrator/index.php?option=com_users&task=user.edit&id=${id}`);

      // Unblock the User
      cy.get('label[for="jform_block1"]').click();
      cy.clickToolbarButton('Save');
      cy.checkForSystemMessage('User saved.');

      // Assert that the Unblock action log entry exists
      cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
      cy.contains('unblocked user').should('be.visible');
    });
  });

  it('logs blocking / unblocking directly from the List table view (backend)', () => {
    cy.db_createUser().then(() => {
      cy.visit('/administrator/index.php?option=com_users&view=users');

      // Search for the created user
      cy.get('#filter_search').clear().type('test@example.com');
      cy.get('.btn-group .btn').first().click();

      cy.get('input[type="checkbox"][name="cid[]"]').first().check();

      // Click "Block" from the list toolbar
      cy.clickToolbarButton('Action');
      cy.contains('Block').click();

      cy.checkForSystemMessage('User blocked.');

      // Verify Action Log
      cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
      cy.contains('blocked user').should('be.visible');

      // Go back to the user list to test unblocking
      cy.visit('/administrator/index.php?option=com_users&view=users');
      cy.get('#filter_search').clear().type('test@example.com');
      cy.get('.btn-group .btn').first().click();
      cy.get('input[type="checkbox"][name="cid[]"]').first().check();

      // Click "Unblock" from the list toolbar
      cy.clickToolbarButton('Action');
      cy.contains('Unblock').click();
      cy.checkForSystemMessage('User unblocked.');

      // Verify Action Log
      cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
      cy.contains('unblocked user').should('be.visible');
    });
  });
});
