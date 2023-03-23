describe('Test that the Contact Form', () => {
  it('can display a Contact Form', () => {
    cy.db_getUserId().then((id) => {
      cy.db_createContact({ name: 'contact 1', user_id: id, featured: 1 })
        .then((contactId) => {
          cy.visit(`index.php?option=com_contact&view=contact&id='${contactId}'`);
          cy.contains('Contact Form');
          cy.get('.m-0').should('exist');
        });
    });
  });
});
