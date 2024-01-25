describe('Test that menu items administrator API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__menu WHERE title = 'automated test administrator menu item' "));

  it('can deliver a list of administrator menu items types', () => {
    cy.api_get('/menus/administrator/items/types')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('type')
        .should('include', 'menutypes'));
  });

  it('can deliver a list of administrator menu items', () => {
    cy.db_createMenuItem({ title: 'automated test administrator menu item', client_id: 1 })
      .then(() => cy.api_get('/menus/administrator/items'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test administrator menu item'));
  });

  it('can deliver a single administrator menu item', () => {
    cy.db_createMenuItem({ title: 'automated test administrator menu item', client_id: 1 })
      .then((id) => cy.api_get(`/menus/administrator/items/${id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test administrator menu item'));
  });

  it('can create an administrator menu item', () => {
    cy.api_post('/menus/administrator/items', {
      title: 'automated test administrator menu item',
      menutype: 'main-menu',
      access: '1',
      parent_id: '1',
      client_id: 1,
      publish_down: '',
      publish_up: '',
      published: '1',
      template_style_id: '0',
      toggle_modules_assigned: '1',
      toggle_modules_published: '1',
      type: 'component',
      alias: '',
      link: '',
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test administrator menu item'));
  });

  it('can update an administrator menu item', () => {
    cy.db_createMenuItem({ title: 'automated test administrator menu item', type: 'component', client_id: 1 })
      .then((id) => cy.api_patch(`/menus/administrator/items/${id}`, { title: 'updated automated test administrator menu item', type: 'component' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test administrator menu item'));
  });

  it('can delete an administrator menu item', () => {
    cy.db_createMenuItem({ title: 'automated test administrator menu item', published: -2, client_id: 1 })
      .then((id) => cy.api_delete(`/menus/administrator/items/${id}`));
  });
});
