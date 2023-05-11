describe('Test in frontend that the privacy confirm request view', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_config');

    cy.get('#configTabs > :nth-child(1) > [aria-controls="page-server"]').click();
    cy.get('joomla-field-send-test-mail > .options-form > .form-grid > :nth-child(1) > .control-label').should('contain.text', 'Send Mail');

    cy.get('#jform_mailonline1').check();
    cy.get('.button-save').click();
  });

  it('can create a form to submit an information request of type export', () => {
    cy.doFrontendLogin();
    cy.visit('/index.php?option=com_privacy&view=request');

    cy.get('#jform_request_type').select('Export');
    cy.get('.controls > .btn').click();
    cy.get('.alert-message').should('contain.text', 'Your information request has been created. Before it can be processed, you must verify this request. An email has been sent to your address with additional instructions to complete this verification.');
  });
});
