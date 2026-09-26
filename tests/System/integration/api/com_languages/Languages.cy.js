describe('Test that languages API endpoint', () => {
  it('can deliver a list of languages', () => {
    cy.api_get('/languages')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('type')
        .should('include', 'languages'));
  });
});
