describe('Test that content categories API endpoint', () => {
  it('can display a list of categories', () => {
    cy.db_createCategory({ title: 'automated test category', extension: 'com_banners' })
      .then((id) => cy.db_createBanner({ name: 'automated test banner', catid: id }))
      .then(() => cy.api_get('/banners/categories'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test category'));
  });
});
