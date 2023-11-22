describe('Test that the API', () => {
  it('returns unauthorized return code when a malformed token is set', () => {
    cy.request({
      method: 'GET', url: '/api/index.php/v1/content/articles', headers: { Authorization: 'Bearer 123' }, failOnStatusCode: false,
    })
      .its('status').should('equal', 401);
  });

  it('returns unauthorized return code when no token is set', () => {
    cy.request({ method: 'GET', url: '/api/index.php/v1/content/articles', failOnStatusCode: false })
      .its('status').should('equal', 401);
  });

  it('returns not found return code when wrong route is set', () => {
    cy.api_getBearerToken().then((token) => cy.request({
      method: 'GET', url: '/api/index.php/v1/content/articles/1', headers: { Authorization: `Bearer ${token}` }, failOnStatusCode: false,
    })
      .its('status').should('equal', 404));
  });
});
