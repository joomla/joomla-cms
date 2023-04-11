describe('Test that the form view ', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__contact_details'));

  it('Can create contact through form view', () => {
    cy.doFrontendLogin();
    cy.visit('index.php?option=com_contact&view=form&layout=edit');
    cy.get('#jform_name').type('test contact 1');
    cy.get('.mb-2 > .btn-primary').click();
    cy.visit('index.php?option=com_contact&view=category&id=4');

    cy.contains('test contact 1').should('exist');
  });
});
