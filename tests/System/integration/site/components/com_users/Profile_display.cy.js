describe('Test the user profile view', () => {
  it('can display user profile for test user', () => {
    cy.db_createUser({ username: 'test', password: '098f6bcd4621d373cade4e832627b4f6' })
      .then(() => {
        cy.visit('index.php?option=com_users&view=profile');

        cy.get('#username').type('test');
        cy.get('#password').type('test');
        cy.get('#remember').check();
        cy.get('.controls > .btn').click();

        cy.get('#system-message-container').should('contain.text', 'You have been logged in.');
        cy.get('#users-profile-core').should('contain.text', 'Profile');
        cy.get('#users-profile-core').should('contain.text', 'Name');
        cy.get('#users-profile-core').should('contain.text', 'test user');
        cy.get('#users-profile-core').should('contain.text', 'Username');
        cy.get('#users-profile-core').should('contain.text', 'test');
        cy.get('#users-profile-core').should('contain.text', 'Registered Date');
        cy.get('#users-profile-core').should('contain.text', 'Wednesday, 01 March 2023');
      });
  });

  it('testing user profile display for test user through menu item', () => {
    cy.db_createUser({ username: 'test', password: '098f6bcd4621d373cade4e832627b4f6' })
      .then(() => cy.db_createMenuItem({ title: 'Automated test user profile', link: 'index.php?option=com_users&view=profile' }))
      .then(() => {
        cy.visit('/');

        cy.get('a:contains(Automated test user profile)').click();
        cy.get('#username').type('test');
        cy.get('#password').type('test');
        cy.get('#remember').check();
        cy.get('.controls > .btn').click();

        cy.get('#system-message-container').should('contain.text', 'You have been logged in.');
        cy.get('#users-profile-core').should('contain.text', 'Profile');
        cy.get('#users-profile-core').should('contain.text', 'Name');
        cy.get('#users-profile-core').should('contain.text', 'test user');
        cy.get('#users-profile-core').should('contain.text', 'Username');
        cy.get('#users-profile-core').should('contain.text', 'test');
        cy.get('#users-profile-core').should('contain.text', 'Registered Date');
        cy.get('#users-profile-core').should('contain.text', 'Wednesday, 01 March 2023');
      });
  });
});
