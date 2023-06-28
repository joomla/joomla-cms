describe('Test that content categories API endpoint', () => {
  it('can display a list of categories', () => {
    cy.db_createCategory({ title: 'automated test category', extension: 'com_content' })
      .then((id) => cy.db_createArticle({ title: 'automated test article', catid: id }))
      .then(() => cy.api_get('/content/categories'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test category'));
  });
});
