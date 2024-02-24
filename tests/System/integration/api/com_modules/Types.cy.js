describe('Test that modules types API endpoint', () => {
  it('can deliver a list of modules types administrator', () => {
    cy.api_get('/modules/types/administrator')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('module')
        .should('include', 'mod_latestactions'));
  });

  it('can deliver a list of modules types site', () => {
    cy.api_get('/modules/types/site')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('module')
        .should('include', 'mod_articles_archive'));
  });
});
