describe('Test that menus site API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__menu_types WHERE title = 'automated test site menu' "));

  it('can deliver a list of site menus', () => {
    cy.db_createMenuType({ title: 'automated test site menu' })
      .then(() => cy.api_get('/menus/site'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test site menu'));
  });

  it('can deliver a single site menu', () => {
    cy.db_createMenuType({ title: 'automated test site menu' })
      .then((id) => cy.api_get(`/menus/site/${id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test site menu'));
  });

  it('can create a site menu', () => {
    cy.api_post('/menus/site', {
      client_id: 0,
      description: 'The menu for the site',
      menutype: 'menu',
      title: 'automated test site menu',
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test site menu'));
  });

  it('can update a site menu', () => {
    cy.db_createMenuType({ title: 'automated test site menu' })
      .then((id) => cy.api_patch(`/menus/site/${id}`, { title: 'updated automated test site menu' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test site menu'));
  });

  it('can delete a site menu', () => {
    cy.db_createMenuType({ title: 'automated test site menu' })
      .then((id) => cy.api_delete(`/menus/site/${id}`));
  });
});
