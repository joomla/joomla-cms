describe('Test the registration view',() => {
    it('can display the registration view for test user', () =>{
        cy.db_createUser({
          name: 'test user', username: 'test', email: 'test@example.com', password: '098f6bcd4621d373cade4e832627b4f6',
          })
          .then(() => {
            cy.visit('http://localhost/joomla/index.php?option=com_users&view=registration');
  
            cy.get('#username').type('test');
            cy.get('#password').type('test');
            cy.get('#remember').check();
            cy.get('.controls > .btn').click();
            cy.get('.alert-wrapper').should('contain.text', 'You have been logged in.');
          });
    });
  
    it('testing registration form for test user through menu item', () => {
      cy.db_createUser({
        name: 'test user', username: 'test', email: 'test@example.com', password: '098f6bcd4621d373cade4e832627b4f6',
        })
        .then(() => cy.db_createMenuItem({ title: 'Automated test registration', link: 'index.php?option=com_users&view=registration', path: '?option=com_users&view=registration' }))
        .then(() => {
            cy.visit('http://localhost/joomla');
            cy.get('a:contains(Automated test registration)').click();
            cy.get('#username').type('test');
            cy.get('#password').type('test');
            cy.get('#remember').check();
            cy.get('.controls > .btn').click();
            cy.get('.alert-wrapper').should('contain.text', 'You have been logged in.');
        });
    });
  })
  