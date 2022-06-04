// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Test com_content Field features', () => {
  before(function () {
    cy.doAdministratorLogin(this.joomlaconfig.username, this.joomlaconfig.password)
  })

  it('Field', function () {
    cy.createField('text', 'Field title')
    cy.trashField('Field title', 'Field trashed')
    cy.deleteField('Field title', 'Field deleted')
  })
})
