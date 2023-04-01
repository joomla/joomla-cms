describe('Test the Login view for test user ', () => {
  it('can login successfully for test user ', () => {
    cy.db_createUser({
      name: 'automated test user',
      username: 'test',
      email: 'test@example.com',
      password: '098f6bcd4621d373cade4e832627b4f6',
    }).then(() => {
      cy.visit('/');
      cy.get('#username').type('test');
      cy.get('#password').type('test');
      cy.get('#remember').check();
      cy.get('.controls > .btn').click();
      cy.get('.alert-wrapper').should('contain.text', 'You have been logged in.');
    });
  });
});
