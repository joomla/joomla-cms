describe('Test that the menu items API endpoint checks the permissions of the menu', () => {
  const denied = JSON.stringify({
    'core.create': { 6: 0 }, 'core.edit': { 6: 0 }, 'core.edit.state': { 6: 0 }, 'core.delete': { 6: 0 },
  });

  let menusRules;
  let manager;
  let allowedMenu;
  let deniedMenu;
  let allowedItem;
  let deniedItem;
  let mainItem;

  const itemData = (menutype, title) => ({
    title,
    alias: title.replace(/ /g, '-'),
    menutype,
    type: 'component',
    link: 'index.php?option=com_content&view=featured',
    parent_id: 1,
    published: 1,
    language: '*',
    client_id: 0,
  });

  before(() => {
    // The manager group is not allowed to access the menus component and to use it in the API by default
    cy.task('queryDB', 'SELECT rules FROM #__assets WHERE name = \'com_menus\'').then((rows) => {
      menusRules = rows[0].rules;

      return cy.task('queryDB', 'UPDATE #__assets SET rules = \'{"core.admin":{"7":1},"core.manage":{"6":1},"core.api":{"6":1}}\' WHERE name = \'com_menus\'');
    });

    // Two menus, the manager is not allowed to create, edit or delete items in the second one
    cy.api_post('/menus/site', {
      client_id: 0, menutype: 'ci-acl-allowed', title: 'ci acl allowed', description: 'ci acl',
    }).then((response) => {
      allowedMenu = Number(response.body.data.id);
    });
    cy.api_post('/menus/site', {
      client_id: 0, menutype: 'ci-acl-denied', title: 'ci acl denied', description: 'ci acl',
    }).then((response) => {
      deniedMenu = Number(response.body.data.id);

      return cy.task('queryDB', `UPDATE #__assets SET rules = '${denied}' WHERE name = 'com_menus.menu.${deniedMenu}'`);
    });
  });

  // The database cleanup after each test removes the user and the menu items, so they are created for each test
  beforeEach(() => {
    cy.db_createUser({
      group_id: 6, username: 'apimanager787', name: 'apimanager787', email: 'apimanager787@example.com',
    }).then((userId) => cy.api_getBearerToken(userId)).then((token) => {
      manager = { token };
    });
    cy.api_post('/menus/site/items', itemData('ci-acl-allowed', `ci acl allowed item ${Date.now()}`)).then((response) => {
      allowedItem = Number(response.body.data.id);
    });
    cy.db_createMenuItem({ title: 'ci acl denied item', alias: 'ci-acl-denied-item', menutype: 'ci-acl-denied', link: 'index.php?option=com_content&view=featured',
    }).then((id) => {
      deniedItem = id;
    });
    cy.db_createMenuItem({
      title: 'ci acl main item', alias: 'ci-acl-main-item', menutype: 'main', client_id: 1,
    }).then((id) => {
      mainItem = id;
    });
  });

  after(() => {
    if (menusRules) {
      cy.task('queryDB', `UPDATE #__assets SET rules = '${menusRules}' WHERE name = 'com_menus'`);
    }

    cy.task('queryDB', 'DELETE FROM #__menu WHERE title LIKE \'ci acl%\'');
    cy.task('queryDB', 'DELETE FROM #__menu_types WHERE title LIKE \'ci acl%\'');
    cy.task('queryDB', 'DELETE FROM #__assets WHERE title LIKE \'ci acl%\'');
  });

  it('allows to create, edit and delete menu items in a menu with the permission', () => {
    cy.api_requestAs(manager.token, 'POST', '/menus/site/items', itemData('ci-acl-allowed', 'ci acl created'))
      .its('status').should('eq', 200);
    cy.api_requestAs(manager.token, 'PATCH', `/menus/site/items/${allowedItem}`, { title: 'ci acl allowed edited', type: 'component' })
      .its('status').should('eq', 200);
    cy.api_requestAs(manager.token, 'PATCH', `/menus/site/items/${allowedItem}`, { published: -2, type: 'component' })
      .its('status').should('eq', 200);
    cy.api_requestAs(manager.token, 'DELETE', `/menus/site/items/${allowedItem}`)
      .its('status').should('eq', 204);
  });

  it('does not allow to create a menu item in a menu without the permission', () => {
    cy.api_requestAs(manager.token, 'POST', '/menus/site/items', itemData('ci-acl-denied', 'ci acl denied created'))
      .its('status').should('eq', 403);
    cy.task('queryDB', 'SELECT id FROM #__menu WHERE title = \'ci acl denied created\'').should('have.length', 0);
  });

  it('does not allow to edit or delete a menu item in a menu without the permission', () => {
    cy.api_requestAs(manager.token, 'PATCH', `/menus/site/items/${deniedItem}`, { title: 'ci acl denied edited' })
      .its('status').should('eq', 403);
    cy.task('queryDB', `UPDATE #__menu SET published = -2 WHERE id = ${deniedItem}`);
    cy.api_requestAs(manager.token, 'DELETE', `/menus/site/items/${deniedItem}`)
      .its('status').should('eq', 403);
    cy.task('queryDB', `SELECT title FROM #__menu WHERE id = ${deniedItem}`)
      .then((rows) => expect(rows[0].title).to.eq('ci acl denied item'));
  });

  it('does not allow to move a menu item into a menu without the permission', () => {
    cy.api_requestAs(manager.token, 'PATCH', `/menus/site/items/${allowedItem}`, { menutype: 'ci-acl-denied', link: 'index.php?option=com_content&view=featured',
    })
      .its('status').should('eq', 403);
    cy.task('queryDB', `SELECT menutype FROM #__menu WHERE id = ${allowedItem}`)
      .then((rows) => expect(rows[0].menutype).to.eq('ci-acl-allowed'));
  });

  it('does not allow to create or edit items of the main menu', () => {
    cy.api_requestAs(manager.token, 'POST', '/menus/administrator/items', {
      ...itemData('main', 'ci acl main created'), link: 'index.php?option=com_banners', client_id: 1,
    }).its('status').should('eq', 403);
    cy.api_requestAs(manager.token, 'PATCH', `/menus/administrator/items/${mainItem}`, { title: 'ci acl main edited' })
      .its('status').should('eq', 403);
    cy.task('queryDB', `SELECT title FROM #__menu WHERE id = ${mainItem}`)
      .then((rows) => expect(rows[0].title).to.eq('ci acl main item'));
  });
});
