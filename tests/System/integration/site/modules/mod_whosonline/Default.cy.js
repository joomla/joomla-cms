describe('Test in frontend that the who is online module', () => {
  it('can display who is online', () => {
    cy.db_createModule({ title: 'Who is Online', module: 'mod_whosonline' })
      .then(() => {
        cy.visit('/');
        cy.contains('Who is Online');
      });
  });
});
