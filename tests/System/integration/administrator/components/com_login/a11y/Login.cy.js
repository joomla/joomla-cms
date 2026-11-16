describe('Test accessibility on login and Home dashboard', () => {
  it('has no detectable a11y violations on login view when password is visible', () => {
    cy.visit('/administrator');
    cy.get('#mod-login-username').type('username');
    cy.get('#mod-login-password').type('password');
    cy.contains('Show Password').click();
    cy.runAccessibilityScan();
  });

  it('has no detectable a11y violations on login view when a required field is missing', () => {
    cy.visit('/administrator');
    cy.get('#mod-login-password').type('password');
    cy.get('#btn-login-submit').click();
    cy.get('#system-message-container').should('be.visible');
    cy.runAccessibilityScan();
  });

  it('has no detectable a11y violations on login view when wrong credentials are provided', () => {
    cy.visit('/administrator');
    cy.get('#mod-login-username').type('username');
    cy.get('#mod-login-password').type('password');
    cy.get('#btn-login-submit').click();
    cy.get('#system-message-container').should('be.visible');
    cy.runAccessibilityScan();
  });

  it('has no detectable a11y violations on Home dashboard', () => {
    cy.doAdministratorLogin();
    cy.visit('/administrator');
    cy.runAccessibilityScan();
  });
});
