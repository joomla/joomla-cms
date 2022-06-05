// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Test general features of Joomla', () => {
  it('Login/Logout in Frontend', function () {
    cy.doFrontendLogin(this.joomlaconfig.username, this.joomlaconfig.password)
    cy.doFrontendLogout()
  })
})
