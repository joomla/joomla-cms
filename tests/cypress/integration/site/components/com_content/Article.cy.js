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
        cy.get('a').click();
        cy.contains('automated test article', 'h2');
    });
  });
});
