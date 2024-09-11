describe('Test that newsfeed categories API endpoint', () => {
  it('can deliver a list of categories', () => {
    cy.db_createCategory({ title: 'automated test category', extension: 'com_newsfeeds' })
      .then((id) => cy.db_createNewsFeed({ name: 'automated test feed', catid: id }))
      .then(() => cy.api_get('/newsfeeds/categories'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test category'));
  });

  it('can deliver a single category', () => {
    cy.db_createCategory({ title: 'automated test feed category', extension: 'com_newsfeeds' })
      .then((id) => cy.api_get(`/newsfeeds/categories/${id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test feed category'));
  });

  it('can create a category', () => {
    cy.api_post('/newsfeeds/categories', { title: 'automated test feed category', description: 'automated test feed category description' })
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', 'automated test feed category');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('description')
          .should('include', 'automated test feed category description');
      });
  });

  it('can update a category', () => {
    cy.db_createCategory({ title: 'automated test feed category', extension: 'com_newsfeeds' })
      .then((id) => cy.api_patch(`/newsfeeds/categories/${id}`, { title: 'updated automated test feed category', description: 'automated test feed category description' }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', 'updated automated test feed category');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('description')
          .should('include', 'automated test feed category description');
      });
  });
});
