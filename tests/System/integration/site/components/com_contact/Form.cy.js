describe('Test that the Contact Form', () => {
  it('can display a Contact Form', () => {
    cy.task('queryDB', `SELECT id FROM #__users WHERE username = '${Cypress.env('username')}'`)
      .then((id) => {
        cy.db_createContact({ name: 'contact 1', user_id: id[0].id })
          .then(() => cy.db_createMenuItem({ title: 'automated test', link: 'index.php?option=com_contact&view=category&id=4', path: '?option=com_contact&view=category&id=4' }))
          .then(() => {
            cy.visit('/');
            cy.get('a:contains(automated test)').click();
            cy.get('a:contains(contact 1)').click();

            cy.contains('Contact Form');
            cy.get('.m-0').should('exist');
          });
      });
  });
});
