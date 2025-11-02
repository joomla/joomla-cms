describe('Test in frontend that the users profile view', () => {
  it('can display a user profile for a test user without a menu item', () => {
    cy.db_createUser({
      name: 'automated test user', username: 'automatedtestuser', password: '098f6bcd4621d373cade4e832627b4f6', registerDate: '2023-03-01 20:00:00',
    })
      .then(() => {
        cy.doFrontendLogin('automatedtestuser', 'test', false);
        cy.visit('/index.php?option=com_users&view=profile');

        cy.get('#users-profile-core').should('contain.text', 'Profile');
        cy.get('#users-profile-core').should('contain.text', 'Name');
        cy.get('#users-profile-core').should('contain.text', 'automated test user');
        cy.get('#users-profile-core').should('contain.text', 'Username');
        cy.get('#users-profile-core').should('contain.text', 'automatedtestuser');
        cy.get('#users-profile-core').should('contain.text', 'Registered Date');
        cy.get('#users-profile-core').should('contain.text', 'Wednesday, 01 March 2023');
      });
  });

  it('can display a user profile for a test user in a menu item', () => {
    cy.db_createUser({
      name: 'automated test user', username: 'automatedtestuser', password: '098f6bcd4621d373cade4e832627b4f6', registerDate: '2023-03-01 20:00:00',
    })
      .then(() => cy.db_createMenuItem({ title: 'Automated test user profile', link: 'index.php?option=com_users&view=profile' }))
      .then(() => {
        cy.doFrontendLogin('automatedtestuser', 'test', false);
        cy.visit('/');
        cy.get('a:contains(Automated test user profile)').click();

        cy.get('#users-profile-core').should('contain.text', 'Profile');
        cy.get('#users-profile-core').should('contain.text', 'Name');
        cy.get('#users-profile-core').should('contain.text', 'automated test user');
        cy.get('#users-profile-core').should('contain.text', 'Username');
        cy.get('#users-profile-core').should('contain.text', 'automatedtestuser');
        cy.get('#users-profile-core').should('contain.text', 'Registered Date');
        cy.get('#users-profile-core').should('contain.text', 'Wednesday, 01 March 2023');
      });
  });
});
