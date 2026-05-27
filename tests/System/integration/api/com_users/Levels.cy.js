describe('Test that users access levels API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__viewlevels WHERE title LIKE 'automated test level%'"));

  it('can deliver a list of user access levels', () => {
    cy.api_get('/users/levels')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
      .its('title')
      .should('include', 'Public'));
  });

  it('can deliver a single users access level', () => {
    cy.db_createUserLevel({ title: 'automated test level'})
      .then((level) => cy.api_get(`/users/levels/${level.id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test level'));
  });

  it('can create a users access level', () => {
    cy.api_post('/users/levels', {
      id: '0',
      title: 'automated test level',
      rules: [1],
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test level'));
  });

  it('can update a users access level', () => {
    cy.db_createUserLevel({ title: 'automated test level'})
      .then((level) => cy.api_patch(`/users/levels/${level.id}`, { title: 'updated automated test level' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test level'));
  });

  it('can delete a user access level', () => {
    cy.db_createUserLevel({ title: 'automated test level'})
      .then((level) => cy.api_delete(`/users/levels/${level.id}`));
  });
});
