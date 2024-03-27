describe('Test that content categories API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__categories WHERE title = 'automated test content category'"));

  it('can deliver a list of categories', () => {
    cy.db_createCategory({ title: 'automated test content category', extension: 'com_content' })
      .then((id) => cy.db_createArticle({ title: 'automated test article', catid: id }))
      .then(() => cy.api_get('/content/categories'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test content category'));
  });

  it('can deliver a single category', () => {
    cy.db_createCategory({ title: 'automated test content category', extension: 'com_content' })
      .then((id) => cy.api_get(`/content/categories/${id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test content category'));
  });

  it('can create a category', () => {
    cy.api_post('/content/categories', { title: 'automated test content category', description: 'automated test content category description' })
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', 'automated test content category');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('description')
          .should('include', 'automated test content category description');
      });
  });

  it('can update a category', () => {
    cy.db_createCategory({ title: 'automated test content category', extension: 'com_content' })
      .then((id) => cy.api_patch(`/content/categories/${id}`, { title: 'updated automated test content category', description: 'automated test content category description' }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', 'updated automated test content category');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('description')
          .should('include', 'automated test content category description');
      });
  });
});
