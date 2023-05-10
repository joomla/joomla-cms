describe('Test in frontend that the privacy confirm request view', () => {
  it('can create a form to submit an information request of type export', () => {
    cy.db_createUser({
      name: 'automated test user 01', username: 'automatedtestuser01', password: '098f6bcd4621d373cade4e832627b4f6', email: 'test@example.com',
    })
      .then(() => {
        cy.doFrontendLogin('automatedtestuser01', 'test', false);
        cy.visit('/index.php?option=com_privacy&view=request');

        cy.get('#jform_request_type').select('Export');
        cy.get('.controls > .btn').click();
        cy.get('.alert-message').should('contain.text', 'Your information request has been created. Before it can be processed, you must verify this request. An email has been sent to your address with additional instructions to complete this verification.');
      });
  });
});
