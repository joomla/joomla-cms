describe('Test that the Action Logs plugin', () => {
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

  it('logs user registration from frontend', () => {
    cy.db_updateExtensionParameter('allowUserRegistration', '1', 'com_users');
    // Visit frontend Registration page
    cy.visit('/index.php?option=com_users&view=registration');

    // Fill out Frontend Registration Form
    cy.get('#jform_name').type('test user');
    cy.get('#jform_username').type('test');
    cy.get('#jform_password1').type('Password123!');
    cy.get('#jform_password2').type('Password123!');
    cy.get('#jform_email1').type('frontendtest@example.com');
    
    cy.get('button[type="submit"]').contains('Register').click();

    // Go back to backend to check action log
    cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
    
    // Asserts that user registration was logged
    cy.contains('registered').should('be.visible');
    cy.db_updateExtensionParameter('allowUserRegistration', '0', 'com_users');
  });

  it('logs editing profile from frontend', () => {
    cy.db_createUser().then(() => {
      // Visit frontend login, input credentials (assuming default db_createUser password is 'test')
      cy.visit('/index.php?option=com_users&view=login');
      cy.get('#username').type('test');
      cy.get('#password').type('test');
      cy.get('button[type="submit"]').contains('Log in').click();
      cy.reload();

      // Navigate to Frontend edit profile view
      cy.visit('/index.php?option=com_users&view=profile&layout=edit');

      // Update the profile field
      cy.get('#jform_name').clear().type('test edited frontend');
      cy.get('button[type="submit"]').contains('Save').click();

      // Go back to backend to check action log
      cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
      cy.contains('updated the user').should('be.visible');
    });
  });

  it('logs username remind request from frontend', () => {
    cy.db_createUser().then(() => {
      // Visit frontend Remind page
      cy.visit('/index.php?option=com_users&view=remind');

      // Clear input and submit user email address with safe inputs
      cy.get('#jform_email')
        .should('be.visible')
        .clear({ force: true })
        .type('test@example.com', { force: true });

      cy.get('button[type="submit"]').contains('Submit').click();

      // Verify logging on the administrative backend
      cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
      cy.contains('requested a username reminder for their account ').should('be.visible');
    });
  });

  it('logs password reset request from frontend', () => {
    cy.db_createUser().then(() => {
      // Visit frontend Reset page
      cy.visit('/index.php?option=com_users&view=reset');

      // Clear input and submit user email address with safe inputs
      cy.get('#jform_email')
        .should('be.visible')
        .clear({ force: true })
        .type('test@example.com', { force: true });

      cy.get('button[type="submit"]').contains('Submit').click();

      // Verify logging on the administrative backend
      cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
      cy.contains('requested a password reset for their account ').should('be.visible');
    });
  });
});
