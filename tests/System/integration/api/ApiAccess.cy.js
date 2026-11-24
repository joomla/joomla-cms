describe('Test that the API checks the permission to use the component in the web services (core.api)', () => {
  const assets = ['com_content', 'com_categories', 'com_users', 'com_privacy'];
  const rules = {};
  let manager;
  let category;
  let article;

  before(() => {
    assets.forEach((name) => cy.task('queryDB', `SELECT rules FROM #__assets WHERE name = '${name}'`).then((rows) => {
      rules[name] = rows[0].rules;
    }));

    cy.db_createCategory({ title: 'ci api access', extension: 'com_content' })
      .then((id) => {
        category = id;

        return cy.api_post('/content/articles', {
          title: 'ci api access', alias: 'ci-api-access', catid: id, introtext: 'test', state: 1, access: 1, language: '*',
        });
      })
      .then((response) => {
        article = Number(response.body.data.id);
      });
  });

  // The database cleanup after each test removes the user, so it is created for each test
  beforeEach(() => {
    // The manager group may manage the content component in the back end, but not use it in the API
    cy.api_setAssetRules('com_content', { 'core.manage': { 6: 1 }, 'core.api': null });

    cy.db_createUser({
      group_id: 6, username: 'apimanager761', name: 'apimanager761', email: 'apimanager761@example.com',
    }).then((userId) => cy.api_getBearerToken(userId)).then((token) => {
      manager = { token };
    });
  });

  after(() => {
    Object.entries(rules).forEach(([name, previous]) => cy.task('queryDB', `UPDATE #__assets SET rules = '${previous}' WHERE name = '${name}'`));
    cy.task('queryDB', `DELETE FROM #__content WHERE id = ${article}`);
    cy.task('queryDB', `DELETE FROM #__categories WHERE id = ${category}`);
    cy.task('queryDB', 'DELETE FROM #__history WHERE item_id = \'com_content.article.' + article + '\'');
  });

  it('denies a component without the API permission, even with the manage permission', () => {
    cy.api_requestAs(manager.token, 'GET', '/content/articles').its('status').should('eq', 403);
    cy.api_requestAs(manager.token, 'GET', `/content/articles/${article}`).its('status').should('eq', 403);
  });

  it('allows a component with the API permission', () => {
    cy.api_setAssetRules('com_content', { 'core.api': { 6: 1 } });

    cy.api_requestAs(manager.token, 'GET', '/content/articles').its('status').should('eq', 200);
    cy.api_requestAs(manager.token, 'GET', `/content/articles/${article}`).its('status').should('eq', 200);
  });

  it('does not require the API permission for a super user', () => {
    cy.api_get('/content/articles').its('status').should('eq', 200);
  });

  it('checks the API permission of the extension of the categories, not of com_categories', () => {
    cy.api_setAssetRules('com_categories', { 'core.manage': { 6: 1 }, 'core.api': { 6: 1 } });
    cy.api_requestAs(manager.token, 'GET', '/content/categories').its('status').should('eq', 403);

    cy.api_setAssetRules('com_content', { 'core.api': { 6: 1 } });
    cy.api_requestAs(manager.token, 'GET', '/content/categories').its('status').should('eq', 200);
  });

  it('checks the API permission of the component of the fields context', () => {
    cy.api_requestAs(manager.token, 'GET', '/fields/content/articles').its('status').should('eq', 403);

    cy.api_setAssetRules('com_content', { 'core.api': { 6: 1 } });
    cy.api_requestAs(manager.token, 'GET', '/fields/content/articles').its('status').should('eq', 200);

    // The users component is not allowed in the API, the permission for com_content does not apply to it
    cy.api_setAssetRules('com_users', { 'core.manage': { 6: 1 }, 'core.api': null });
    cy.api_requestAs(manager.token, 'GET', '/fields/users').its('status').should('eq', 403);
  });

  it('checks the API permission of the component of the content history', () => {
    cy.api_requestAs(manager.token, 'GET', `/content/articles/${article}/contenthistory`).its('status').should('eq', 403);

    cy.api_setAssetRules('com_content', { 'core.api': { 6: 1 } });
    cy.api_requestAs(manager.token, 'GET', `/content/articles/${article}/contenthistory`).its('status').should('eq', 200);
  });

  it('allows the privacy requests only with the admin permission of the privacy component', () => {
    cy.api_setAssetRules('com_privacy', { 'core.manage': { 6: 1 }, 'core.api': { 6: 1 }, 'core.admin': null });
    cy.api_requestAs(manager.token, 'GET', '/privacy/requests').its('status').should('eq', 403);

    cy.api_setAssetRules('com_privacy', { 'core.admin': { 6: 1 } });
    cy.api_requestAs(manager.token, 'GET', '/privacy/requests').its('status').should('eq', 200);
  });
});
