beforeEach(() => {
  cy.task('queryDB', 'DELETE FROM #__content');
  cy.task('queryDB', 'DELETE FROM #__categories WHERE id > 7');
  cy.task('queryDB', 'DELETE FROM #__user_profiles');
});

describe('Test that content API endpoint', () => {
  it('can deliver a list of articles', () => {
    cy.db_createArticle({ title: 'automated test article' }).then(() => cy.db_getBearerToken())
      .then((token) => {cy.log(Cypress.config('baseUrl') + '/api/index.php/v1/content/articles');cy.pause();
        return cy.request({
          method: 'GET',
          url: Cypress.config('baseUrl') + '/api/index.php/v1/content/articles',
          headers: { bearer: token }
        });
      })
      .then((res) => {
        console.log(res);
        cy.contains('automated test article');
      });
  });
});
