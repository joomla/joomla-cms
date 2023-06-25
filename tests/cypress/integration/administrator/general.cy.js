// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Test general features of Joomla Backend', () => {
  it('Login/Logout in Frontend', function () {
    cy.doAdministratorLogin(Cypress.env('username'), Cypress.env('password'))
    cy.doAdministratorLogout()
  })
})
