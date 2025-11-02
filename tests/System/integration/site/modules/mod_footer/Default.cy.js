describe('Test in frontend that the footer module', () => {
  it('can display the copyright message from Joomla', () => {
    cy.db_createModule({ module: 'mod_footer' }).then(() => {
      cy.visit('/');

      cy.contains('All Rights Reserved');
    });
  });
});
