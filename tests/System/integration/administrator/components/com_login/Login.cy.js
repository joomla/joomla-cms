describe('Test in backend that the login component', () => {
  it('can log in and out', () => {
    cy.visit('administrator/index.php');
    cy.get('#mod-login-username').type(Cypress.env('username'));
    cy.get('#mod-login-password').type(Cypress.env('password'));
    cy.get('#btn-login-submit').click();

    cy.get('h1.page-title').should('contain', 'Home Dashboard');

    cy.get('.header-item .header-profile > .dropdown-toggle').click();
    cy.get('.header-item .header-profile a.dropdown-item:last-child').click();

    cy.get('#mod-login-username').should('exist');
  });

  it('can not log in with wrong username', () => {
    cy.visit('administrator/index.php');
    cy.get('#mod-login-username').type('invalid');
    cy.get('#mod-login-password').type(Cypress.env('password'));
    cy.get('#btn-login-submit').click();

    cy.get('#system-message-container').contains('Username and password do not match or you do not have an account yet.').should('exist');
  });

  it('can not log in with wrong password', () => {
    cy.visit('administrator/index.php');
    cy.get('#mod-login-username').type(Cypress.env('username'));
    cy.get('#mod-login-password').type('invalid');
    cy.get('#btn-login-submit').click();

    cy.get('#system-message-container').contains('Username and password do not match or you do not have an account yet.').should('exist');
  });

  it('can not log in with not existing user', () => {
    cy.visit('administrator/index.php');
    cy.get('#mod-login-username').type('invalid');
    cy.get('#mod-login-password').type('invalid');
    cy.get('#btn-login-submit').click();

    cy.get('#system-message-container').contains('Username and password do not match or you do not have an account yet.').should('exist');
  });
});
