describe('Test that field users API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__viewlevels where title = "automated test level";'));

  it('can deliver a list of levels', () => {
    cy.api_get('/users/levels')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'Public'));
  });

  it('can deliver a single level', () => {
    cy.api_get('/users/levels/1')
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'Public'));
  });

  it('can create a level', () => {
    cy.api_post('/users/levels', {
      id: '0',
      title: 'automated test level',
      rules: '[1]',
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test level'));
  });
});
