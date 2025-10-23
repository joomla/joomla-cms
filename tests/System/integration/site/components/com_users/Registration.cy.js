describe('Test in frontend that the users registration view', () => {
  beforeEach(() => {
    cy.db_updateExtensionParameter('allowUserRegistration', '1', 'com_users');
    cy.task('clearEmails');
  });
  afterEach(() => {
    cy.db_updateExtensionParameter('allowUserRegistration', '0', 'com_users');
    cy.task('queryDB', "DELETE FROM #__user_usergroup_map WHERE user_id = (SELECT id FROM #__users WHERE username = 'testuser')");
    cy.task('queryDB', "DELETE FROM #__users WHERE username = 'testuser'");
  });

  it('can display a registration form for a test user without a menu item', () => {
    cy.visit('/index.php?option=com_users&view=registration');

    cy.get('.com-users-registration__form').should('contain.text', 'User Registration');
    cy.get('#jform_name').clear().type('test user');
    cy.get('#jform_username').clear().type('testuser');
    cy.get('#jform_password1').clear().type(`${Cypress.env('password')}-test`);
    cy.get('#jform_password2').clear().type(`${Cypress.env('password')}-test`);
    cy.get('#jform_email1').clear().type('testuser@example.com');
    cy.get('#member-registration').submit();
    cy.checkForSystemMessage('Your account has been created and a verification link has been sent to the email address you entered.');

    cy.task('getMails').then((mails) => {
      cy.wrap(mails).should('have.lengthOf', 1);
      cy.wrap(mails[0].headers.subject).should('have.string', `Account Details for test user at ${Cypress.env('sitename')}`);
      cy.wrap(mails[0].headers.to).should('equal', 'testuser@example.com');
      cy.wrap(mails[0].body).should('have.string', `Hello test user,\n\nThank you for registering at ${Cypress.env('sitename')}.`);
      cy.wrap(mails[0].body).should('match', /http\S+\?task=registration\.activate&token=[a-z0-9]+/);
      cy.wrap(/http\S+\?task=registration\.activate&token=[a-z0-9]+/.exec(mails[0].body)[0]).as('activatelink');
      cy.wrap(mails[0].html).should('be.false');
    });
    cy.get('@activatelink').then((url) => cy.visit(url));
    cy.checkForSystemMessage('Your email address has been verified.');

    cy.task('getMails').then((mails) => {
      cy.wrap(mails).should('have.lengthOf', 2);
      cy.wrap(mails[1].headers.subject).should('have.string', `Registration approval required for account of test user at ${Cypress.env('sitename')}`);
      cy.wrap(mails[1].headers.to).should('equal', Cypress.env('email'));
      cy.wrap(mails[1].body).should('have.string', `Hello administrator,\n\nA new user has registered at ${Cypress.env('sitename')}.`);
      cy.wrap(mails[1].body).should('have.string', 'Name :  test user');
      cy.wrap(mails[1].body).should('have.string', 'email:  testuser@example.com');
      cy.wrap(mails[1].body).should('have.string', 'Username:  testuser');
      cy.wrap(mails[1].body).should('match', /http\S+\?task=registration\.activate&token=[a-z0-9]+/);
      cy.wrap(/http\S+\?task=registration\.activate&token=[a-z0-9]+/.exec(mails[1].body)[0]).as('activatelinkadmin');
      cy.wrap(mails[1].html).should('be.false');
    });
    cy.get('@activatelinkadmin').then((url) => cy.visit(url));
    cy.checkForSystemMessage('Please log in to confirm that you are authorised to activate new accounts.');

    cy.doFrontendLogin();
    cy.get('@activatelinkadmin').then((url) => cy.visit(url));
    cy.checkForSystemMessage('The user\'s account has been activated and the user has been notified about it.');
    cy.doFrontendLogout();
    cy.task('getMails').then((mails) => {
      cy.wrap(mails).should('have.lengthOf', 3);
      cy.wrap(mails[2].headers.subject).should('have.string', `Account activated for test user at ${Cypress.env('sitename')}`);
      cy.wrap(mails[2].headers.to).should('equal', 'testuser@example.com');
      cy.wrap(mails[2].body).should('have.string', 'Hello test user,\n\nYour account has been activated by an administrator.');
      cy.wrap(mails[2].html).should('be.false');
    });
    cy.doFrontendLogin('testuser', `${Cypress.env('password')}-test`);
    cy.doFrontendLogout();
  });

  it('can display a registration form for a test user in a menu item', () => {
    cy.db_createMenuItem({ title: 'Automated test registration', link: 'index.php?option=com_users&view=registration' })
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(Automated test registration)').click();

        cy.get('.com-users-registration__form').should('contain.text', 'User Registration');
      });
  });
});
