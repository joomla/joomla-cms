describe('Test that contact categories API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__categories WHERE title = 'automated test contact category'"));

  it('can display a list of categories', () => {
    cy.db_createCategory({ title: 'automated test contact category', extension: 'com_contact' })
      .then((id) => cy.db_createContact({ name: 'automated test contact', catid: id }))
      .then(() => cy.api_get('/contacts/categories'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test contact category'));
  });

  it('can display a single category', () => {
    cy.db_createCategory({ title: 'automated test contact category', extension: 'com_contact' })
      .then((id) => cy.api_get(`/contacts/categories/${id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test contact category'));
  });

  it('can create a category', () => {
    cy.api_post('/contacts/categories', { title: 'automated test contact category', description: 'automated test contact category description' })
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', 'automated test contact category');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('description')
          .should('include', 'automated test contact category description');
      });
  });

  it('can update a category', () => {
    cy.db_createCategory({ title: 'automated test contact category', extension: 'com_contact' })
      .then((id) => cy.api_patch(`/contacts/categories/${id}`, { title: 'updated automated test contact category', description: 'automated test contact category description' }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', 'updated automated test contact category');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('description')
          .should('include', 'automated test contact category description');
      });
  });
});
