describe('Test that the front page', () => {
    it('can display banners', () => {
      cy.db_createBanner({ custombannercode: 'automated test banner 1',type: 1})
        .then(() => cy.db_createBanner({ custombannercode: 'automated test banner 2' ,type: 1}))
        .then(() => cy.db_createBanner({ custombannercode: 'automated test banner 3' ,type: 1}))
        .then(() => cy.db_createBanner({ custombannercode: 'automated test banner 4' ,type: 1}))
        .then(() => cy.db_createModule({ title: 'automated test',module: 'mod_banners',position: 'sidebar-right',params:'{"count":5}'}))
        .then(() => {
          cy.visit('/joomla-cms/');

      cy.contains('automated test banner 1');
      cy.contains('automated test banner 2');
      cy.contains('automated test banner 3');
      cy.contains('automated test banner 4');
    });
});
  });