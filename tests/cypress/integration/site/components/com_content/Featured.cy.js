afterEach(function() {
  cy.task('queryDB', 'DELETE FROM #__content');
  cy.task('queryDB', 'DELETE FROM #__content_frontpage');
});

describe('Test that the front page', () => {
  it('can display an article', function () {
    cy.db_createArticle({'title': 'automated test article'}).then((article) => {
        cy.visit('/');

        cy.contains('automated test article');
    });
  });

  it('can navigate to the article', function () {
    cy.db_createArticle({'title': 'automated test article'}).then((article) => {
        cy.visit('/');
        cy.get('.item-title a').click();

        cy.contains('h2', 'automated test article');
    });
  });

  it('can navigate to the category', function () {
    cy.db_createArticle({'title': 'automated test article'}).then((article) => {
        cy.visit('/');
        cy.get('.category-name a').click();

        cy.contains('h1', 'Uncategorised');
    });
  });
});
