describe('Test that templates API endpoint', () => {
  it('can deliver a list of templates', () => {
    cy.api_get('/templates/styles/site')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('template')
        .should('include', 'cassiopeia'));
  });

  it('can deliver a single template', () => {
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

  it('can modify a single template', () => {
    cy.api_get('/templates/styles/site')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id'))
      .then((id) => {
        const updatedStyle = {
          title: 'automated test template site style',
        };
        cy.api_patch(`/templates/styles/site/${id}`, updatedStyle)
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('title')
            .should('equal', 'automated test template site style'));
      });
  });
});
