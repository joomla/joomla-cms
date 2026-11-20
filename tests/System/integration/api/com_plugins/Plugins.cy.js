describe('Test that plugins API endpoint', () => {
  it('can deliver a list of plugins', () => {
    cy.api_get('/plugins')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('folder')
        .should('include', 'actionlog'));
  });

  it('can deliver a single plugin', () => {
    cy.api_get('/plugins')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id'))
      .then((id) => {
        cy.api_get(`/plugins/${id}`)
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('folder')
            .should('include', 'actionlog'));
      });
  });

  it('can modify a single plugin', () => {
    cy.api_get('/plugins')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id'))
      .then((id) => {
        const updatedPlugin = {
          enabled: 0,
        };
        cy.api_patch(`/plugins/${id}`, updatedPlugin)
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('enabled')
            .should('equal', 0));
      });
    cy.db_enableExtension('1', 'plg_actionlog_joomla');
  });
});
