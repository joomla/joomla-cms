describe('Test view article on front page', () => {
  it('views the front page', function () {
    cy.db_createArticle({'title': 'automated test article'}).then((article) => {
        cy.visit('/');

        cy.contains('automated test article');
    });
  });

  it('navigates to the article from the front page', function () {
    cy.db_createArticle({'title': 'automated test article'}).then((article) => {
        cy.visit('/');
        cy.get('.item-title a').click();

        cy.contains('h2', 'automated test article');
    });
  });

  it('navigates to the category from the front page', function () {
    cy.db_createArticle({'title': 'automated test article'}).then((article) => {
        cy.visit('/');
        cy.get('.category-name a').click();

        cy.contains('h1', 'Uncategorised');
    });
  });
});
