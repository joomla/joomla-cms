describe('Test that menu items site API endpoint', () => {
  beforeEach(() => cy.db_deleteMenuItem({ title: 'automated test site menu item' }));

  it('can deliver a list of site menu items types', () => {
    cy.api_get('/menus/site/items/types')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('type')
        .should('include', 'menutypes'));
  });

  it('can deliver a list of site menu items', () => {
    cy.db_createMenuItem({ title: 'automated test site menu item' })
      .then(() => cy.api_get('/menus/site/items'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test site menu item'));
  });

  it('can deliver a single site menu item', () => {
    cy.db_createMenuItem({ title: 'automated test site menu item' })
      .then((id) => cy.api_get(`/menus/site/items/${id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test site menu item'));
  });

  it('can create a site menu item', () => {
    cy.api_post('/menus/site/items', {
      title: 'automated test site menu item',
      menutype: 'main-menu',
      access: '1',
      parent_id: '1',
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
        .should('include', 'automated test site menu item'));
  });

  it('rejects a site menu item with an already used alias', () => {
    cy.db_createMenuItem({ title: 'automated test site menu item', alias: 'automated-test-alias' })
      .then(() => cy.api_getBearerToken())
      .then((token) => cy.request({
        method: 'POST',
        url: '/api/index.php/v1/menus/site/items',
        headers: { Authorization: `Bearer ${token}` },
        json: true,
        failOnStatusCode: false,
        body: {
          title: 'automated test site menu item 2',
          menutype: 'mainmenu',
          access: '1',
          parent_id: '1',
          published: '1',
          type: 'component',
          alias: 'automated-test-alias',
          link: '',
        },
      }))
      .then((response) => cy.wrap(response).its('status').should('be.within', 400, 499));
  });

  it('can update a site menu item', () => {
    cy.db_createMenuItem({ title: 'updated automated test site menu item', type: 'component' })
      .then((id) => cy.api_patch(`/menus/site/items/${id}`, { title: 'automated test site menu item', type: 'component', menuordering: id }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test site menu item'));
  });

  it('can delete a site menu item', () => {
    cy.db_createMenuItem({ title: 'automated test site menu item', published: -2 })
      .then((id) => cy.api_delete(`/menus/site/items/${id}`))
      .then((response) => cy.wrap(response).its('status').should('equal', 204));
  });
});
