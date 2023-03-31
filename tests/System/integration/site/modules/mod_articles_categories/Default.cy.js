describe('Test the articles categories module', () => {
  it('can load in frontend and showing the title of the categories', () => {
    cy.db_createCategory({ title: 'automated test category', parent_id: '2' })
      .then(() => cy.db_createModule({ module: 'mod_articles_categories', params: JSON.stringify({ parent: 2 }) }))
      .then(() => {
        cy.visit('/');

        cy.contains('li', 'automated test category');
      });
  });
});
