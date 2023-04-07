describe('Test the reset view',() => {
    it('can display the reset view for test user', () =>{
        cy.db_createUser({
          name: 'test user', username: 'test', email: 'test@example.com', password: '098f6bcd4621d373cade4e832627b4f6',
          })
          .then(() => {
            cy.visit('index.php?option=com_users&view=reset');
    
            cy.get('#jform_email').type('test@example.com');
            cy.get('.controls > .btn').click();
    
            cy.get('#system-message-container').should('contain.text', 'If the email address you entered is registered on this site you will shortly receive an email with a link to reset the password for your account.');
          });
    });
    
    it('testing reset form for test user through menu item', () => {
      cy.db_createUser({
        name: 'test user', username: 'test', email: 'test@example.com', password: '098f6bcd4621d373cade4e832627b4f6',
        })
        .then(() => cy.db_createMenuItem({ title: 'Automated test reset', link: 'index.php?option=com_users&view=reset&id=1&layout=default', path: '?option=com_users&view=reset&id=1&layout=default' }))
        .then(() => {
          cy.visit('/');
    
          cy.get('a:contains(Automated test reset)').click();
          cy.get('#jform_email').type('test@example.com');
          cy.get('.controls > .btn').click();
    
          cy.get('#system-message-container').should('contain.text', 'If the email address you entered is registered on this site you will shortly receive an email with a link to reset the password for your account.');
        });
    });
  })
