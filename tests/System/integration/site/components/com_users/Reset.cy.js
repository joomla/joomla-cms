describe('Test in frontend that the users reset view', () => {
  beforeEach(() => cy.task('clearEmails'));

  it('can send a reset email for a test user without a menu item', () => {
    cy.db_createUser({ email: 'test@example.com' })
      .then(() => {
        cy.visit('/index.php?option=com_users&view=reset');
        cy.get('#jform_email').type('test@example.com');
        cy.get('.controls > .btn').click();

        cy.task('getMails').then((mails) => {
          cy.checkForSystemMessage('If the email address you entered is registered on this site you will shortly receive an email with a link to reset the password for your account.');

          expect(mails.length).to.equal(1);
          cy.wrap(mails[0].body).should('have.string', 'To reset your password, you will need to submit this verification code');
          cy.wrap(mails[0].body).should('have.string', '/component/users/reset');
          cy.wrap(mails[0].sender).should('equal', Cypress.env('email'));
          cy.wrap(mails[0].receivers).should('have.property', 'test@example.com');
        });
      });
  });

  it('can send a reset email for a test user in a menu item', () => {
    cy.db_createUser({ email: 'test@example.com' })
      .then(() => cy.db_createMenuItem({
        title: 'Automated test reset', alias: 'test-reset', path: 'test-reset', link: 'index.php?option=com_users&view=reset',
      }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(Automated test reset)').click();
        cy.get('#jform_email').type('test@example.com');
        cy.get('.controls > .btn').click();

        cy.task('getMails').then((mails) => {
          cy.checkForSystemMessage('If the email address you entered is registered on this site you will shortly receive an email with a link to reset the password for your account.');

          expect(mails.length).to.equal(1);
          cy.wrap(mails[0].body).should('have.string', 'To reset your password, you will need to submit this verification code');
          cy.wrap(mails[0].body).should('have.string', '/test-reset');
          cy.wrap(mails[0].sender).should('equal', Cypress.env('email'));
          cy.wrap(mails[0].receivers).should('have.property', 'test@example.com');
        });
      });
  });

  it('can not send a reset email for a user which is not registered', () => {
    cy.visit('/index.php?option=com_users&view=reset');
    cy.get('#jform_email').type('test@example.com');
    cy.get('.controls > .btn').click();

    cy.task('getMails').then((mails) => {
      cy.checkForSystemMessage('If the email address you entered is registered on this site you will shortly receive an email with a link to reset the password for your account.');

      expect(mails.length).to.equal(0);
    });
  });

  it('can show the profile page when logged in', () => {
    cy.doFrontendLogin();
    cy.visit('/index.php?option=com_users&view=reset');
    cy.get('#users-profile-core').should('contain.text', 'Profile');
  });
});
