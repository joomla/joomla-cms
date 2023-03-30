describe('Test the Login View of com_users ', () => {
    it('can display the login form for a user', () => {
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
          cy.get('.alert-wrapper').should(
            "contain.text","You have been logged in."
          )
        });
    });
  });