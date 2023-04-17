describe('Test in backend that the login component', () => {
  it('can log in and out', () => {
    cy.doAdministratorLogin(null, null, false);
    cy.doAdministratorLogout();
  });
});
