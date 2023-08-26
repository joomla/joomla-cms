describe('Test in frontend that the wrapper view', () => {
  it('can display wrapper', () => {
    cy.db_createMenuItem({ title: 'automated test wrapper', link: 'index.php?option=com_wrapper&view=wrapper', params: JSON.stringify({ url: 'http://localhost/administrator/index.php' }) })
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test wrapper)').click();

        cy.get('.com-wrapper__iframe').should('exist');
      });
  });
});
