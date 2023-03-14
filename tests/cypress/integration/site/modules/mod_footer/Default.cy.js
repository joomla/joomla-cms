describe('Test that the news module', () => {
  it('can load in frontend and showing the title of the articles', () => {
    cy.db_createModule({ module: 'mod_footer' }).then(() => {
      cy.visit('/');

      cy.contains('All Rights Reserved');
    });
  });
});
