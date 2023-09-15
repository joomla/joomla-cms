describe('Test that config API endpoint', () => {
  it('can deliver a list of application config', () => {
    cy.api_get('/config/application')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id')
        .should('equal', 228));
  });

  it('can modify a single application config', () => {
    const updatedConfig = {
      offline: true,
    };
    cy.api_patch('/config/application', updatedConfig)
      .then((response) => cy.wrap(response).its('status').should('equal', 200));
  });

  it('can deliver a config application', () => {
    cy.api_get('/config/com_content')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id')
        .should('equal', 19));
  });

  it('can modify a single config application', () => {
    const updatedConfig = {
      show_title: '0',
    };
    cy.api_patch('/config/com_content', updatedConfig)
      .then((response) => cy.wrap(response).its('status').should('equal', 200));
  });
});
