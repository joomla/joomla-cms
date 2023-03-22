describe('Test that the Contact Form', () => {
  it('can display a Contact Form', () => {
    cy.db_getUserId().then((id) => {
      cy.db_createContact({ name: 'contact 1', user_id: id, featured: 1 })
        .then(() => {
          cy.task('queryDB', 'SELECT id FROM #__contact_details WHERE name = \'contact 1\'')
            .then(async (contactId) => {
              cy.visit(`index.php?option=com_contact&view=contact&id='${contactId[0].id}'`);
            });

          cy.contains('Contact Form');
          cy.get('.m-0').should('exist');
        });
    });
  });
});
