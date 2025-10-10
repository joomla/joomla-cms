describe('Test that templates administrator styles API endpoint', () => {
  it('can deliver a list of templates administrator styles', () => {
    cy.api_get('/templates/styles/administrator')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('template')
        .should('include', 'atum'));
  });

  it('can deliver a single templates administrator style', () => {
    cy.api_get('/templates/styles/administrator')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id'))
      .then((id) => {
        cy.api_get(`/templates/styles/administrator/${id}`)
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('template')
            .should('include', 'atum'));
      });
  });

  it('can modify a single template administrator style', () => {
    cy.api_get('/templates/styles/administrator')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id'))
      .then((id) => {
        const updatedStyle = {
          title: 'automated test template administrator style',
        };
        cy.api_patch(`/templates/styles/administrator/${id}`, updatedStyle)
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('title')
            .should('equal', 'automated test template administrator style'));
      });
  });
});
