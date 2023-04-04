describe('Test the articles category module', () => {
  it('can load in frontend and showing the title of the articles', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then(() => cy.db_createModule({ module: 'mod_articles_category', params: JSON.stringify({ catid: 2 }) }))
      .then(() => {
        cy.visit('/');

        cy.contains('li', 'automated test article');
      });
  });
});
