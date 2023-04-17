describe('Test that the custom module', () => {
  it('can load in frontend and shows the custom text', () => {
    cy.db_createModule({ module: 'mod_custom', content: '<p>Automated test text</p>' }).then(() => {
      cy.visit('/');

      cy.contains('Automated test text');
    });
  });
});
