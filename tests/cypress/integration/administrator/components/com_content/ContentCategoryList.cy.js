// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Test com_content Category features', () => {
  before(() => {
<<<<<<< HEAD
    cy.doAdministratorLogin(Cypress.env('username'), Cypress.env('password'))
=======
    cy.doAdministratorLogin(this.joomlaconfig.username, this.joomlaconfig.password)
>>>>>>> 91839b1858 (Migrating a bunch of acceptance tests to cypress)
  })

  it('Category', function () {
    cy.createContentCategory('Category title')
  })
})
