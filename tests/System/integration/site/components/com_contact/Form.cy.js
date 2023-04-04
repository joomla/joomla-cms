describe('Test that the form view ', () => {
  it('can display a form view of contact', () => {
    cy.doFrontendLogin(Cypress.env('username'), Cypress.env('password'));
    cy.visit('index.php?option=com_contact&view=form&layout=edit');
    cy.get('#jform_name').type('test contact 1');
    cy.get('.mb-2 > .btn-primary').click();
    cy.visit('index.php?option=com_contact&view=category&id=4');
    
    cy.contains('test contact 1').should('exist');
   });
});
