describe('Test in frontend that the users registration view', () => {
  it('can display a registration form for a test user without a menu item', () => {
    cy.db_createUser({ username: 'test', password: '098f6bcd4621d373cade4e832627b4f6' })
      .then(() => {
        cy.visit('/index.php?option=com_users&view=registration');
        cy.get('#username').type('test');
        cy.get('#password').type('test');
        cy.get('#remember').check();
        cy.get('.controls > .btn').click();

        cy.get('#system-message-container').should('contain.text', 'You have been logged in.');
      });
  });

  it('can display a registration form for a test user in a menu item', () => {
    cy.db_createUser({ username: 'test', password: '098f6bcd4621d373cade4e832627b4f6' })
      .then(() => cy.db_createMenuItem({ title: 'Automated test registration', link: 'index.php?option=com_users&view=registration' }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(Automated test registration)').click();
        cy.get('#username').type('test');
        cy.get('#password').type('test');
        cy.get('#remember').check();
        cy.get('.controls > .btn').click();

        cy.get('#system-message-container').should('contain.text', 'You have been logged in.');
      });
  });
});
