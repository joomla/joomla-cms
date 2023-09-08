describe('Test that modules API endpoint', () => {
    afterEach(() => cy.task('queryDB', "DELETE FROM #__modules WHERE title = 'automated test administrator module'"));

    it('can deliver a list of administrator modules', () => {
      cy.api_get('/modules/administrator')
        .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('module')
          .should('include', 'mod_sampledata'));
    });

    it('can deliver a single administrator module', () => {
      cy.db_createModule({ title: 'automated test administrator module', client_id: 1 })
        .then((module) => cy.api_get(`/modules/administrator/${module}`))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', 'automated test administrator module'));
    });
});