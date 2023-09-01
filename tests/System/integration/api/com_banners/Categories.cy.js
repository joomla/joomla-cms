describe('Test that banners categories API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__categories WHERE title = 'automated test banner category'"));

  it('can display a list of categories', () => {
    cy.db_createCategory({ title: 'automated test banner category', extension: 'com_banners' })
      .then((id) => cy.db_createBanner({ name: 'automated test banner', catid: id }))
      .then(() => cy.api_get('/banners/categories'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test banner category'));
  });

  it('can display a single category', () => {
    cy.db_createCategory({ title: 'automated test banner category', extension: 'com_banners' })
      .then((id) => cy.api_get(`/banners/categories/${id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test banner category'));
  });

  it('can create a category', () => {
    cy.api_post('/banners/categories', { title: 'automated test banner category', description: 'automated test banner category description' })
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', 'automated test banner category');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('description')
          .should('include', 'automated test banner category description');
      });
  });

  it('can update a category', () => {
    cy.db_createCategory({ title: 'automated test banner category', extension: 'com_banners' })
      .then((id) => cy.api_patch(`/banners/categories/${id}`, { title: 'updated automated test banner category', description: 'automated test banner category description' }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', 'updated automated test banner category');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('description')
          .should('include', 'automated test banner category description');
      });
  });
});
