describe('Test that the popular articles module', () => {
  it('can load in frontend and shows the title of the test article ', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then(() => cy.db_createModule({ module: 'mod_articles_popular', params: JSON.stringify({ catid: 2 }) }))
      .then(() => {
        cy.visit('/');

        cy.contains('li', 'automated test article');
      });
  });
});
