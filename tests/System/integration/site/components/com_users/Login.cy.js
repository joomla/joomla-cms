describe('Test in frontend that the users login view', () => {
  it('can log in a test user without a menu item', () => {
    cy.db_createUser({ username: 'test', password: '098f6bcd4621d373cade4e832627b4f6' })
      .then(() => {
        cy.visit('/index.php?option=com_users&view=login');
        cy.get('#username').type('test');
        cy.get('#password').type('test');
        cy.get('#remember').check();
        cy.get('.controls > button[type="submit"].btn').click();
        cy.visit('/index.php?option=com_users&view=login');

        cy.get('.com-users-logout').should('contain.text', 'Log out');
      });
  });

  it('can log in a test user in a menu item', () => {
    cy.db_createUser({ username: 'test', password: '098f6bcd4621d373cade4e832627b4f6' })
      .then(() => cy.db_createMenuItem({ title: 'Automated test login', link: 'index.php?option=com_users&view=login' }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(Automated test login)').click();
        cy.get('#username').type('test');
        cy.get('#password').type('test');
        cy.get('#remember').check();
        cy.get('.controls > button[type="submit"].btn').click();
        cy.get('a:contains(Automated test login)').click();

        cy.get('.com-users-logout').should('contain.text', 'Log out');
      });
  });
});
