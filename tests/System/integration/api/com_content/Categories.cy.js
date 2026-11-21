describe('Test that content categories API endpoint', () => {
  afterEach(() => cy.db_deleteCategory({ title: 'automated test content category' }));

  it('can deliver a list of categories', () => {
    cy.db_createCategory({ title: 'automated test content category', extension: 'com_content' })
      .then((id) => cy.db_createArticle({ title: 'automated test article', catid: id }))
      .then(() => cy.api_get('/content/categories'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test content category'));
  });

  it('can deliver a list of published categories', () => {
    cy.db_createCategory({ title: 'automated test content category', extension: 'com_content', published: 1 })
      .then((id) => cy.db_createArticle({ title: 'automated test article', catid: id }))
      .then(() => cy.api_get('/content/categories?filter[state]=1'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test content category'));
  });

  it('can deliver a list of unpublished categories', () => {
    cy.db_createCategory({ title: 'automated test content category', extension: 'com_content', published: 0 })
      .then((id) => cy.db_createArticle({ title: 'automated test article', catid: id }))
      .then(() => cy.api_get('/content/categories?filter[state]=0'))
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
    cy.api_post('/content/categories', {
      title: 'automated test content category',
      description: 'automated test content category description',
      parent_id: 1,
      extension: 'com_content',
    })
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
      .then((id) => cy.api_patch(`/content/categories/${id}`, {
        title: 'updated automated test content category',
        description: 'automated test content category description',
      }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', 'updated automated test content category');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('description')
          .should('include', 'automated test content category description');
      });
  });

  it('cannot delete a trashed category which still has articles', () => {
    cy.api_post('/content/categories', { title: 'automated test content category', parent_id: 1, extension: 'com_content' })
      .then((response) => response.body.data.id)
      .then((id) => cy.api_patch(`/content/categories/${id}`, { published: -2 })
        .then(() => cy.db_createArticle({ title: 'automated test article', catid: Number(id) }))
        .then(() => cy.api_getBearerToken())
        .then((token) => cy.request({
          method: 'DELETE',
          url: `/api/index.php/v1/content/categories/${id}`,
          headers: { Authorization: `Bearer ${token}` },
          failOnStatusCode: false,
        }))
        .then((response) => {
          expect(response.status).to.equal(409);
          expect(response.body.messages.error[0]).to.include('Delete not allowed for category automated test content category');
        })
        .then(() => cy.task('queryDB', `SELECT id FROM #__categories WHERE id = ${id}`))
        .then((rows) => expect(rows).to.have.length(1)));
  });
});
