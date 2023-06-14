describe('Test that tags API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__tags'));

  it('can deliver a list of tags', () => {
    cy.db_createTag({ title: 'automated test tag' })
      .then(() => cy.api_get('/tags'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test tag'));
  });

  it('can update a tag', () => {
    cy.db_createTag({ title: 'automated test tag' })
      .then((id) => cy.api_patch(`/tags/${id}`, { title: 'updated automated test tag' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test tag'));
  });

  it('can delete a tag', () => {
    cy.db_createTag({ title: 'automated test tag', published: -2 })
      .then((id) => cy.api_delete(`/tags/${id}`));
  });
});
