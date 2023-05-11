describe('Test in frontend that the articles categories module', () => {
  it('can display the title of the categories', () => {
    cy.db_createCategory({ title: 'automated test category' })
      .then(() => cy.db_createModule({ module: 'mod_articles_categories' }))
      .then(() => {
        cy.visit('/');

        cy.contains('li', 'automated test category');
      });
  });
});
