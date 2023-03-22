describe('Test that the news module', () => {
  it('can load in frontend and showing the title of the articles', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then(() => cy.db_createModule({ module: 'mod_articles_news', params: JSON.stringify({ item_title: 1, item_heading: 'h3' }) }))
      .then(() => {
        cy.visit('/');

        cy.contains('h3', 'automated test article');
      });
  });
});
