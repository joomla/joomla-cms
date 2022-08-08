// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Test com_content Field features', () => {
  before(function () {
<<<<<<< HEAD
    cy.doAdministratorLogin(Cypress.env('username'), Cypress.env('password'))
=======
    cy.doAdministratorLogin(this.joomlaconfig.username, this.joomlaconfig.password)
>>>>>>> 91839b1858 (Migrating a bunch of acceptance tests to cypress)
  })

  it('Field', function () {
    cy.createField('text', 'Field title')
    cy.trashField('Field title', 'Field trashed')
    cy.deleteField('Field title', 'Field deleted')
  })
})
