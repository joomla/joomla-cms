describe('Test that media adapters API endpoint', () => {
  it('can deliver a list of adapters', () => {
    cy.api_get('/media/adapters')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('provider_id')
        .should('include', 'local'));
  });

  it('can deliver a specific of adapters', () => {
    cy.api_get('/media/adapters/local-images')
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('provider_id')
        .should('include', 'local'));
  });
});
