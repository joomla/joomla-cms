describe('Test that content categories API endpoint', () => {
  it('can display a list of categories', () => {
    cy.db_createCategory({ title: 'automated test category', extension: 'com_banners' })
      .then((id) => cy.db_createBanner({ name: 'automated test banner', catid: id }))
      .then(() => cy.api_get('/banners/categories'))
      .then((response) => cy.wrap(response.body.data.map((category) => ({ title: category.attributes.title })))
        .should('deep.include', { title: 'automated test category' }));
  });
});
