describe('Test that the login component', () => {
  it('can log in and out in the back end', () => {
    cy.doAdministratorLogin(null, null, false);
    cy.doAdministratorLogout();
  });
});
