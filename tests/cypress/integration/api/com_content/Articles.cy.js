beforeEach(() => {
  cy.task('queryDB', 'DELETE FROM #__content');
  cy.task('queryDB', 'DELETE FROM #__categories WHERE id > 7');
  cy.task('queryDB', 'DELETE FROM #__user_profiles');
});

describe('Test that content API endpoint', () => {
  it('can deliver a list of articles', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then(() => cy.api_get('/content/articles'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes').its('title').should('include', 'automated test article'));
  });
});
