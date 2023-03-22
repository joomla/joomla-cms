describe('Test that the footer module', () => {
  it('can load in frontend and shows the copyright message from Joomla', () => {
    cy.db_createModule({ module: 'mod_footer' }).then(() => {
      cy.visit('/');

      cy.contains('All Rights Reserved');
    });
  });
});
