describe('Test in frontend that the custom module', () => {
  it('can display the custom text', () => {
    cy.db_createModule({ module: 'mod_custom', content: '<p>Automated test text</p>' }).then(() => {
      cy.visit('/');

      cy.contains('Automated test text');
    });
  });
});
