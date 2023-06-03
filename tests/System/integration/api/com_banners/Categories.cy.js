describe('Test that banner categories API endpoint', () => {
  it('can display a list of categories', () => {
    cy.db_createCategory({ title: 'automated test category', extension: 'com_banners' })
      .then((id) => cy.db_createBanner({ name: 'automated test banner', catid: id }))
      .then(() => cy.api_get('/banners/categories'))
      .then((response) => cy.wrap(response).its('body').its('data[0]').its('attributes')
        .its('title')
        .should('include', 'automated test category'));
  });
});
