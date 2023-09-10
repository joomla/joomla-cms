describe('Test that templates site styles API endpoint', () => {
  it('can deliver a list of templates site styles', () => {
    cy.api_get('/templates/styles/site')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('template')
        .should('include', 'cassiopeia'));
  });

  it('can deliver a single templates site style', () => {
    cy.api_get('/templates/styles/site')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id'))
      .then((id) => {
        cy.api_get(`/templates/styles/site/${id}`)
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('template')
            .should('include', 'cassiopeia'));
      });
  });

  it('can modify a single templates site styles', () => {
    cy.api_get('/templates/styles/site')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id'))
      .then((id) => {
        const updatedPlugin = {
          title: 'automated test template site style',
        };
        cy.api_patch(`/templates/styles/site/${id}`, updatedPlugin)
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('title')
            .should('equal', 'automated test template site style'));
      });
  });
});
