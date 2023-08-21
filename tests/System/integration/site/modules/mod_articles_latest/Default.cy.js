describe('Test in frontend that the latest articles module', () => {
  it('can load without ordering parameter', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then(() => cy.db_createModule({ module: 'mod_articles_latest' }))
      .then(() => {
        cy.visit('/');

        cy.contains('li', 'automated test article');
      });
  });
});
