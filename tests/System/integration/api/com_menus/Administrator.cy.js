describe('Test that menus administrator API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__menu_types WHERE title = 'automated test administrator menu' "));

  it('can deliver a list of administrator menus', () => {
    cy.api_get('/menus/administrator')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'Main Menu'));
  });

  it('can deliver a single administrator menu', () => {
    cy.db_createMenuType({ title: 'automated test administrator menu', client_id: 1 })
      .then((id) => cy.api_get(`/menus/administrator/${id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test administrator menu'));
  });

  it('can create an administrator menu', () => {
    cy.api_post('/menus/administrator', {
      client_id: 1,
      description: 'The menu for the administrator',
      menutype: 'menu',
      title: 'automated test administrator menu',
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test administrator menu'));
  });

  it('can update an administrator menu', () => {
    cy.db_createMenuType({ title: 'automated test administrator menu', client_id: 1 })
      .then((id) => cy.api_patch(`/menus/administrator/${id}`, { title: 'updated automated test administrator menu' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test administrator menu'));
  });

  it('can delete an administrator menu', () => {
    cy.db_createMenuType({ title: 'automated test administrator menu', client_id: 1 })
      .then((id) => cy.api_delete(`/menus/administrator/${id}`));
  });
});
