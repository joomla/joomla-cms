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
      const tokenGen = /Token: ([a-f0-9]+)/;
      const tokenMatch = mails[1].body.match(tokenGen);
      const token = tokenMatch ? tokenMatch[1] : '';
      cy.wrap(mails[1].body).should('have.string', `Someone has created a request to export all personal information related to this email address at ${Cypress.config('baseUrl')}/. As a security measure, you must confirm that this is a valid request for your personal information from this website.\n\nIf this was a mistake, ignore this email and nothing will happen.\n\nIn order to confirm this request, you can complete one of the following tasks:\n\n1. Visit the following URL: ${Cypress.config('baseUrl')}/index.php/confirm?confirm_token=${token}\n\n2. Copy your token from this email, visit the referenced URL, and paste your token into the form.\nURL: ${Cypress.config('baseUrl')}/index.php/confirm\nToken: ${token}\n\nPlease note that this token is only valid for 24 hours from the time this email was sent.\n`);
    });
  });
});
