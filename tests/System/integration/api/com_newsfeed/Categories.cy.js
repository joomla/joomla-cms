describe('Test that newsfeed categories API endpoint', () => {
  it('can display a list of categories', () => {
    cy.db_createCategory({ title: 'automated test category', extension: 'com_newsfeeds' })
      .then((id) => cy.db_createNewsFeed({ name: 'automated test feed', catid: id }))
      .then(() => cy.api_get('/newsfeeds/categories'))
      .then((response) => cy.wrap(response).its('body').its('data[0]').its('attributes')
        .its('title')
        .should('include', 'automated test category'));
  });
});
