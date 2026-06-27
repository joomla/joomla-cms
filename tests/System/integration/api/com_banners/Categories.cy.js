describe('Test that banners categories API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__categories WHERE title = 'automated test banner category'"));

  it('can deliver a list of categories', () => {
    cy.db_createCategory({ title: 'automated test banner category', extension: 'com_banners' })
      .then((id) => cy.db_createBanner({ name: 'automated test banner', catid: id }))
      .then(() => cy.api_get('/banners/categories'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test banner category'));
  });

  it('can deliver a list of published categories', () => {
    cy.db_createCategory({ title: 'automated test banner category', extension: 'com_banners', published: 1 })
      .then((id) => cy.db_createBanner({ name: 'automated test banner', catid: id }))
      .then(() => cy.api_get('/banners/categories?filter[state]=1'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test banner category'));
  });

  it('can deliver a list of unpublished categories', () => {
    cy.db_createCategory({ title: 'automated test banner category', extension: 'com_banners', published: 0 })
      .then((id) => cy.db_createBanner({ name: 'automated test banner', catid: id }))
      .then(() => cy.api_get('/banners/categories?filter[state]=0'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test banner category'));
  });

  it('can deliver a single category', () => {
    cy.db_createCategory({ title: 'automated test banner category', extension: 'com_banners' })
      .then((id) => cy.api_get(`/banners/categories/${id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test banner category'));
  });

  it('can create a category', () => {
    cy.api_post('/banners/categories', {
      title: 'automated test banner category',
      description: 'automated test banner category description',
      parent_id: 1,
      extension: 'com_banners',
    })
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
