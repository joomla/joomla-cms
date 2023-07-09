describe('Test that the latest articles module', () => {
  it('can load in frontend without ordering parameter', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then(() => cy.db_createModule({ module: 'mod_articles_latest' }))
      .then(() => {
        cy.visit('/');

        cy.contains('li', 'automated test article');
      });
  });
});
