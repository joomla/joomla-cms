describe('Test that users access levels API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__viewlevels WHERE title = 'automated test level'"));

  it('can deliver a list of user access levels', () => {
    cy.db_createUserLevel({ title: 'automated test level'})
      .then(() => cy.api_get('/users/levels'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test level'));
  });

  it('can deliver a single level', () => {
    cy.db_createUserLevel({ title: 'automated test level'})
      .then((id) => cy.api_get(`/users/levels/${id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test level'));
  });

  it('can create a level', () => {
    cy.api_post('/users/levels', {
      title: 'automated test level',
      rules: [1,2]
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test level'));
  });

  it('can update a level', () => {
    cy.db_createUserLevel({ title: 'automated test level'})
      .then((id) => cy.api_patch(`/users/levels/${id}`, { title: 'updated automated test level' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test level'));
  });

  it('can delete a level', () => {
    cy.db_createUserLevel({ title: 'automated test level'})
      .then((id) => cy.api_delete(`/users/levels/${id}`));
  });
});
