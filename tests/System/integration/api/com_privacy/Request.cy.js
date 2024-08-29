describe('Test privacy request API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__privacy_requests'));

  it('can get a list of requests', () => {
    cy.db_createPrivacyRequest()
      .then(() => cy.api_get('/privacy/requests'))
      .then((response) => cy.api_responseContains(response, 'email', 'test@example.com'));
  });

  it('can list a single request', () => {
    cy.db_createPrivacyRequest()
      .then((id) => cy.api_get(`/privacy/requests/${id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('status')
        .should('equal', 0));
  });

  it('can create a request', () => {
    cy.api_post('/privacy/requests ', {
      email: 'test@example.com',
      request_type: 'export',
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('email')
        .should('include', 'test@example.com'));
  });
});
