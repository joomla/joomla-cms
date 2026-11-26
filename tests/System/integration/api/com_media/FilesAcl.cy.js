describe('Test that the media files API endpoint checks the permissions', () => {
  // 1x1 transparent PNG
  const png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';
  const path = 'ci-acl-file.png';
  let mediaRules;
  let manager;
  let superUserToken;

  const rules = (permissions) => JSON.stringify({
    'core.admin': { 7: 1 }, 'core.manage': { 6: 1 }, 'core.api': { 6: 1 }, 'core.create': { 3: 1 }, 'core.delete': { 5: 1 }, ...permissions,
  });

  const setRules = (permissions) => cy.task('queryDB', `UPDATE #__assets SET rules = '${rules(permissions)}' WHERE name = 'com_media'`);

  const exists = () => cy.api_requestAs(superUserToken, 'GET', `/media/files/${path}`).then((response) => response.status === 200);

  before(() => {
    cy.task('queryDB', 'SELECT rules FROM #__assets WHERE name = \'com_media\'').then((rows) => {
      mediaRules = rows[0].rules;
    });
    cy.api_getBearerToken().then((token) => {
      superUserToken = token;
    });
  });

  // The database cleanup after each test removes the user, so it is created for each test
  beforeEach(() => {
    cy.db_createUser({
      group_id: 6, username: 'apimanager786', name: 'apimanager786', email: 'apimanager786@example.com',
    }).then((userId) => cy.api_getBearerToken(userId)).then((token) => {
      manager = { token };
    });
  });

  afterEach(() => {
    cy.task('deleteRelativePath', `files/${path}`);
  });

  after(() => {
    if (mediaRules) {
      cy.task('queryDB', `UPDATE #__assets SET rules = '${mediaRules}' WHERE name = 'com_media'`);
    }
  });

  it('does not allow to create a file without the create permission, with and without the override flag', () => {
    // Managers are allowed to edit, but explicitly denied to create
    setRules({ 'core.create': { 3: 1, 6: 0 }, 'core.edit': { 6: 1 } });

    cy.api_requestAs(manager.token, 'POST', '/media/files', { path, content: png })
      .its('status').should('eq', 403);
    cy.api_requestAs(manager.token, 'POST', '/media/files', { path, content: png, override: true })
      .its('status').should('eq', 403);
    exists().should('eq', false);
  });

  it('allows to create a file with the create permission and to override it with the edit permission', () => {
    setRules({ 'core.create': { 3: 1, 6: 1 }, 'core.edit': { 6: 1 } });

    cy.api_requestAs(manager.token, 'POST', '/media/files', { path, content: png })
      .its('status').should('eq', 200);
    exists().should('eq', true);
    cy.api_requestAs(manager.token, 'POST', '/media/files', { path, content: png, override: true })
      .its('status').should('eq', 200);
  });

  it('does not allow to override a file without the edit permission', () => {
    setRules({ 'core.create': { 3: 1, 6: 1 }, 'core.edit': { 6: 0 } });

    cy.api_requestAs(manager.token, 'POST', '/media/files', { path, content: png })
      .its('status').should('eq', 200);
    cy.api_requestAs(manager.token, 'POST', '/media/files', { path, content: png, override: true })
      .its('status').should('eq', 403);
  });

  it('does not allow to create a file with a PATCH request without a path in the URL without the create permission', () => {
    setRules({ 'core.create': { 3: 1, 6: 0 }, 'core.edit': { 6: 1 } });

    cy.api_requestAs(manager.token, 'PATCH', '/media/files/', { path, content: png })
      .its('status').should('eq', 403);
    // "0" as path in the URL is treated as no path by the model
    cy.api_requestAs(manager.token, 'PATCH', '/media/files/0', { path, content: png })
      .its('status').should('eq', 403);
    exists().should('eq', false);
  });

  it('allows to create a file with a PATCH request without a path in the URL with the create permission', () => {
    setRules({ 'core.create': { 3: 1, 6: 1 }, 'core.edit': { 6: 1 } });

    cy.api_requestAs(manager.token, 'PATCH', '/media/files/', { path, content: png })
      .its('status').should('eq', 200);
    exists().should('eq', true);
  });
});
