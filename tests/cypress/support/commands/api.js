Cypress.Commands.add('api_get', (path) =>
  cy.api_getBearerToken().then((token) =>
    cy.request({ method: 'GET', url: '/api/index.php/v1' + path, headers: { Authorization: 'Bearer ' + token } })
  )
);

Cypress.Commands.add('api_post', (path, body) =>
  cy.api_getBearerToken().then((token) =>
    cy.request({ method: 'POST', url: '/api/index.php/v1' + path, body: body, headers: { Authorization: 'Bearer ' + token }, json: true })
  )
);

Cypress.Commands.add('api_patch', (path, body) =>
  cy.api_getBearerToken().then((token) =>
    cy.request({ method: 'PATCH', url: '/api/index.php/v1' + path, body: body, headers: { Authorization: 'Bearer ' + token }, json: true })
  )
);

Cypress.Commands.add('api_delete', (path) =>
  cy.api_getBearerToken().then((token) =>
    cy.request({ method: 'DELETE', url: '/api/index.php/v1' + path, headers: { Authorization: 'Bearer ' + token }})
  )
);

Cypress.Commands.add('api_getBearerToken', () => {
  return cy.task('queryDB', "SELECT id FROM #__users WHERE username = 'api'").then((id) => {
    if (id.length > 0) {
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
      group_id: 8
    }).then((user) => {
      cy.task(
        'queryDB',
        "INSERT INTO #__user_profiles (user_id, profile_key, profile_value) VALUES "
        + "('" + user.insertId + "', 'joomlatoken.token', 'dOi2m1NRrnBHlhaWK/WWxh3B5tqq1INbdf4DhUmYTI4=')"
      );
      return cy.task(
        'queryDB',
        "INSERT INTO #__user_profiles (user_id, profile_key, profile_value) VALUES ('" + user.insertId + "', 'joomlatoken.enabled', 1)"
      );
    }).then(() => 'c2hhMjU2OjM6ZTJmMjJlYTNlNTU0NmM1MDJhYTIzYzMwN2MxYzAwZTQ5NzJhMWRmOTUyNjY5MTk2YjE5ODJmZWMwZTcxNzgwMQ==');
  });
});
