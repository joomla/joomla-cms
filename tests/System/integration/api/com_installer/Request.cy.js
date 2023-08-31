describe('Test that installer API endpoint', () => {
  it('can deliver a list of extensions', () => {
    cy.api_get('/extensions')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('name')
        .should('include', 'Action Log - Joomla'));
  });
});
