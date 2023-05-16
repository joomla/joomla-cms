describe('Test in frontend that the privacy request view', () => {
  beforeEach(() => cy.task('clearEmails'));

  it('can submit an information request of type export without a menu item', () => {
    cy.doFrontendLogin();
    cy.visit('/index.php?option=com_privacy&view=request');
    cy.get('#jform_request_type').select('Export');
    cy.get('.controls > .btn').click();

    cy.task('getMails').then((mails) => {
      cy.get('.alert-message').should('contain.text', 'Your information request has been created. Before it can be processed, you must verify this request. An email has been sent to your address with additional instructions to complete this verification.');
      cy.wrap(mails).should('have.lengthOf', 2);
      cy.wrap(mails[0].sender).should('equal', Cypress.env('email'));
      cy.wrap(mails[0].body).should('have.string', `A new information request has been submitted by ${Cypress.env('email')}.`);
      cy.wrap(mails[1].body).should('have.string', 'Someone has created a request to export all personal information related to this email address at ');
    });
  });

  it('can submit an information request of type export in a menu item', () => {
    //create a test user as the first test already has a request from the original user
    cy.db_createUser({ username: 'testuser', password: '098f6bcd4621d373cade4e832627b4f6', email: 'test@example.com' })
      .then(() => cy.db_createMenuItem({ title: 'Automated request information', link: 'index.php?option=com_privacy&view=request' }))
      .then(() => {
        cy.doFrontendLogin('testuser', 'test', false);
        cy.visit('/');
        cy.get('a:contains(Automated request information)').click();
        cy.get('#jform_request_type').select('Export');
        cy.get('.controls > .btn').click();

        cy.task('getMails').then((mails) => {
          cy.get('.alert-message').should('contain.text', 'Your information request has been created. Before it can be processed, you must verify this request. An email has been sent to your address with additional instructions to complete this verification.');
          cy.wrap(mails).should('have.lengthOf', 2);
          cy.wrap(mails[0].body).should('have.string', `A new information request has been submitted by ${'test@example.com'}.`);
          cy.wrap(mails[1].body).should('have.string', 'Someone has created a request to export all personal information related to this email address at ');
        });
      });
  });
});
