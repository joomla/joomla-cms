describe('Test that config API endpoint', () => {
  it('can deliver a list of application config', () => {
    cy.api_get('/config/application')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('offline')
        .should('equal', false));
  });

  it('can modify a single application config', () => {
    const updatedConfig = {
      offline: true,
    };
    cy.api_patch('/config/application', updatedConfig)
      .then((response) => cy.wrap(response).its('status').should('equal', 200));
    cy.config_setParameter('offline', false);
  });
});
