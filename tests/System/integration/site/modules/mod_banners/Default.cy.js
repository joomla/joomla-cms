describe('Test in frontend that the banner module', () => {
  it('can display banners', () => {
    cy.db_createBanner({ custombannercode: 'automated test banner 1' })
      .then(() => cy.db_createBanner({ custombannercode: 'automated test banner 2' }))
      .then(() => cy.db_createBanner({ custombannercode: 'automated test banner 3' }))
      .then(() => cy.db_createBanner({ custombannercode: 'automated test banner 4' }))
      .then(() => cy.db_createModule({ title: 'automated test', module: 'mod_banners', params: '{"count":5 }' }))
      .then(() => {
        cy.visit('/');

        cy.contains('automated test banner 1');
        cy.contains('automated test banner 2');
        cy.contains('automated test banner 3');
        cy.contains('automated test banner 4');
      });
  });
});
