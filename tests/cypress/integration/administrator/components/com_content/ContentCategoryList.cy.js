// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Test com_content Category features', () => {
  before(() => {
    cy.doAdministratorLogin(this.joomlaconfig.username, this.joomlaconfig.password)
  })

  it('Category', function () {
    cy.createContentCategory('Category title')
  })
})
