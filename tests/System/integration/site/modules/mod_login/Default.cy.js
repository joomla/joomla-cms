describe('Test that the login module', () => {
  it('can login and logout in Frontend', () => {
    cy.doFrontendLogin(null, null, false);
    cy.doFrontendLogout();
  });
});

describe('Test the Login module for test user ', () => {
  it('can display the login module for a user', () => {
    cy.db_createUser({
      name: 'automated test user',
      username: 'test',
      email: 'test@example.com',
      password: '098f6bcd4621d373cade4e832627b4f6',
    }).then(() => {
      cy.visit('http://localhost/joomla-cms/');
      cy.get('#modlgn-username-16').type('test');
      cy.get('#modlgn-passwd-16').type('test');
      cy.get('input[name="remember"]').check();
      cy.get('.mod-login__submit > .btn').click();
      cy.get('.alert-wrapper').should('contain.text', 'You have been logged in.');
    });
  });
});
