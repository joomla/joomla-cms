describe('Test that config of com_content API endpoint', () => {
  it('can deliver a config of com_content', () => {
    cy.api_get('/config/com_content')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id')
        .should('equal', 19));
  });

  it('can modify a single config of com_content', () => {
    const updatedConfig = {
      show_title: '0',
    };
    const revertConfig = {
      show_title: '1',
    };
    cy.api_patch('/config/com_content', updatedConfig)
      .then((response) => cy.wrap(response).its('status').should('equal', 200));
    cy.api_patch('/config/com_content', revertConfig)
      .then((response) => cy.wrap(response).its('status').should('equal', 200));
  });
});
