describe('Test in frontend that the privacy view confirm request layout', () => {

  beforeEach(() => {
    cy.task('clearEmails');
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_config');

    cy.get('#configTabs div[role="tablist"] button[aria-controls="page-server"]').click();
    cy.get('#jform_mailonline1').check();

    cy.get('#jform_mailer').select('SMTP');
    cy.get('#jform_smtphost').clear().type(Cypress.env('smtp_host'));
    cy.get('#jform_smtpport').clear().type(Cypress.env('smtp_port'));
    cy.get('#jform_smtpsecure').select('none');
    cy.get('#sendtestmail').click();

    cy.task('getMails').then((mails) => {
      cy.get('#system-message-container').should('contain.text', 'The email was sent to');

      cy.wrap(mails).should('have.lengthOf', 1);
      cy.wrap(mails[0].body).should('have.string', 'This is a test mail sent using');
      cy.wrap(mails[0].sender).should('equal', Cypress.env('email'));
    });
  });

  it('can create a form to submit an information request of type export', () => {
    cy.doFrontendLogin();
    cy.visit('/index.php?option=com_privacy&view=request');

    cy.get('#jform_request_type').select('Export');
    cy.get('.controls > .btn').click();
    cy.task('getMails').then((mails) => {
      cy.get('.alert-message').should('contain.text', 'Your information request has been created. Before it can be processed, you must verify this request. An email has been sent to your address with additional instructions to complete this verification.');

      cy.wrap(mails).should('have.lengthOf', 3);
      cy.log(mails);
      cy.wrap(mails[0].sender).should('equal', Cypress.env('email'));
      cy.wrap(mails[1].body).should('have.string', `A new information request has been submitted by ${Cypress.env('email')}.\n`);
    });
  });
});
