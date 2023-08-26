// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Test com_content Field features', () => {
  beforeEach(() => {
    cy.doAdministratorLogin(Cypress.env('username'), Cypress.env('password'))
  })

  it('Field', function () {
    cy.createField('text', 'Field title')
    cy.trashField('Field title', 'Field trashed')
    cy.deleteField('Field title', 'Field deleted')
  })
})
