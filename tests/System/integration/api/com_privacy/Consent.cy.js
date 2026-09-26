describe('Test privacy consent API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__privacy_consents'));

  it('can get a list of consents', () => {
    cy.db_createPrivacyConsent({ body: 'test body' })
      .then(() => cy.api_get('/privacy/consents'))
      .then((response) => cy.api_responseContains(response, 'body', 'test body'));
  });

  it('can list a single consent', () => {
    cy.db_createPrivacyConsent({ body: 'test body' })
      .then((id) => cy.api_get(`/privacy/consents/${id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('body')
        .should('contain', 'test body'));
  });
});
