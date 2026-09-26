describe('Test in frontend that the languages module', () => {
  it('can display languages', () => {
    cy.db_createModule({ title: 'Languages', module: 'mod_languages' })
      .then(() => {
        cy.visit('/');
        cy.contains('Languages');
      });
  });
});
