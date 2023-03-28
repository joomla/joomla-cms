describe('Test that the featured articles menu item type', () => {
  it('can display an article', () => {
    cy.db_createArticle({ title: 'automated test article' }).then(() => {
      cy.visit('/');

      cy.contains('automated test article');
    });
  });

  it('can navigate to the article', () => {
    cy.db_createArticle({ title: 'automated test article' }).then(() => {
      cy.visit('/');
      cy.get('.item-title a').click();

      cy.contains('h2', 'automated test article');
    });
  });

  it('can navigate to the category', () => {
    cy.db_createArticle({ title: 'automated test article' }).then(() => {
      cy.visit('/');
      cy.get('.category-name a').click();

      cy.contains('h1', 'Uncategorised');
    });
  });
});
