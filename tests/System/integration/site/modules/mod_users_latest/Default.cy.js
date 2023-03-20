describe('Test that the users latest module', () => {
  it('can load in frontend and displays the latest registered users', () => {
    cy.db_createUser({ username: 'automatedtestuser' })
      .then(() => cy.db_createModule({ module: 'mod_users_latest' }))
      .then(() => {
        cy.visit('/');

        cy.contains('automatedtestuser');
      });
  });
});
