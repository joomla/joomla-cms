Cypress.Commands.add('api_responseContains', (response, attribute, value) => {
  const items = response.body.data.map((item) => ({ attribute: item.attributes[attribute] }));
  cy.wrap(items).should('deep.include', { attribute: value });
});

Cypress.Commands.add('api_get', (path) => cy.api_getBearerToken().then((token) => cy.request({ method: 'GET', url: `/api/index.php/v1${path}`, headers: { Authorization: `Bearer ${token}` } })));

Cypress.Commands.add('api_post', (path, body) => cy.api_getBearerToken().then((token) => cy.request({
  method: 'POST', url: `/api/index.php/v1${path}`, body, headers: { Authorization: `Bearer ${token}` }, json: true,
})));

Cypress.Commands.add('api_patch', (path, body) => cy.api_getBearerToken().then((token) => cy.request({
  method: 'PATCH', url: `/api/index.php/v1${path}`, body, headers: { Authorization: `Bearer ${token}` }, json: true,
})));

Cypress.Commands.add('api_delete', (path) => cy.api_getBearerToken().then((token) => cy.request({ method: 'DELETE', url: `/api/index.php/v1${path}`, headers: { Authorization: `Bearer ${token}` } })));

Cypress.Commands.add('api_getBearerToken', () => cy.task('queryDB', "SELECT id FROM #__users WHERE username = 'api'").then((user) => {
  if (user.length > 0) {
    return 'c2hhMjU2OjM6ZTJmMjJlYTNlNTU0NmM1MDJhYTIzYzMwN2MxYzAwZTQ5NzJhMWRmOTUyNjY5MTk2YjE5ODJmZWMwZTcxNzgwMQ==';
  }

  return cy.db_createUser({
    id: 3,
    name: 'API',
    email: 'api@example.com',
    username: 'api',
    password: '123',
    block: 0,
    registerDate: '2000-01-01',
    params: '{}',
    group_id: 8,
  }).then((id) => {
    cy.task(
      'queryDB',
      'INSERT INTO #__user_profiles (user_id, profile_key, profile_value) VALUES '
        + `('${id}', 'joomlatoken.token', 'dOi2m1NRrnBHlhaWK/WWxh3B5tqq1INbdf4DhUmYTI4=')`,
    );
    return cy.task(
      'queryDB',
      `INSERT INTO #__user_profiles (user_id, profile_key, profile_value) VALUES ('${id}', 'joomlatoken.enabled', 1)`,
    );
  }).then(() => 'c2hhMjU2OjM6ZTJmMjJlYTNlNTU0NmM1MDJhYTIzYzMwN2MxYzAwZTQ5NzJhMWRmOTUyNjY5MTk2YjE5ODJmZWMwZTcxNzgwMQ==');
}));
