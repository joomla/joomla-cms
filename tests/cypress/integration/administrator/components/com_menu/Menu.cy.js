// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Test com_menu features', () => {
  before(function () {
<<<<<<< HEAD
    cy.doAdministratorLogin(Cypress.env('username'), Cypress.env('password'))
=======
    cy.doAdministratorLogin(this.joomlaconfig.username, this.joomlaconfig.password)
>>>>>>> 91839b1858 (Migrating a bunch of acceptance tests to cypress)
  })

  it('creates a new menu', function () {
    cy.visit('administrator/index.php?option=com_menus&view=menus')
    cy.checkForPhpNoticesOrWarnings()
    cy.get('h1.page-title').should('contain', 'Menus')
    cy.clickToolbarButton('New')
    cy.get('h1.page-title').should('contain', 'Menus: Add')
    cy.checkForPhpNoticesOrWarnings()
    cy.get('#jform_title').type('Test Menu')
    cy.get('#jform_menutype').type('Test')
    cy.get('#jform_menudescription').type('Automated Testing')
    cy.clickToolbarButton('Save')
    cy.get('#system-message-container').contains('Menu saved').should('exist')
  })
})
