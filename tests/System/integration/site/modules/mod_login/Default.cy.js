describe('Test that the login module', () => {
  it('can login and logout in Frontend', () => {
    cy.doFrontendLogin(null, null, false);
    cy.doFrontendLogout();
  });
});
