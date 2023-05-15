describe('Test in frontend that the privacy view confirm request layout', () => {
  beforeEach(() => cy.task('clearEmails'));

  it('can create a form to submit an information request of type export', () => {
    cy.doFrontendLogin();
    cy.visit('/index.php?option=com_privacy&view=request');
    cy.get('#jform_request_type').select('Export');
    cy.get('.controls > .btn').click();

    cy.task('getMails').then((mails) => {
      cy.get('.alert-message').should('contain.text', 'Your information request has been created. Before it can be processed, you must verify this request. An email has been sent to your address with additional instructions to complete this verification.');
      cy.wrap(mails).should('have.lengthOf', 2);
      cy.wrap(mails[0].sender).should('equal', Cypress.env('email'));
      cy.wrap(mails[0].body).should('have.string', `A new information request has been submitted by ${Cypress.env('email')}.\n`);
    });
  });
});
