describe('Test in frontend that the smart search view', () => {
  it('can display smart search', () => {
    cy.db_createMenuItem({ title: 'automated test smart search', link: 'index.php?option=com_finder&view=search' })
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test smart search)').click();

        cy.get('#q').should('exist');
        cy.get('.input-group > .btn-primary').should('exist');
      });
  });
});
