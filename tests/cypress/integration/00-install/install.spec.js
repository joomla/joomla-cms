// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Install Joomla', () => {
  it('Install Joomla', function () {
    cy.installJoomla(this.joomlaconfig)
  })
})
