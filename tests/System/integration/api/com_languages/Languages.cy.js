describe('Test that languages API endpoint', () => {
  it('can deliver a list of languages', () => {
    cy.api_get('/languages').then((response) => {
      if (response?.body?.data.length === 0) {
        cy.wrap(response).its('body.data').should('have.length', 0);
        cy.wrap(response).its('body.links.self').should('include', '/api/index.php/v1/languages');
      } else {
        cy.wrap(response).its('body.data.0.type').should('include', 'languages');
      }
    });
  });
});
