describe('Test that the login module', () => {
  it('can login and logout in Frontend', function () {
    cy.doFrontendLogin(Cypress.env('username'), Cypress.env('password'))
    cy.doFrontendLogout()
  })
})
