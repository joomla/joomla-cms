// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Install Joomla', () => {
  it('Install Joomla', function () {

    cy.exec('rm configuration.php', {failOnNonZeroExit: false})

    let config = {
      "sitename": Cypress.env('sitename'),
      "name": Cypress.env('name'),
      "username": Cypress.env('username'),
      "password": Cypress.env('password'),
      "email": Cypress.env('email'),
      "db_type": Cypress.env('db_type'),
      "db_host": Cypress.env('db_host'),
      "db_user": Cypress.env('db_user'),
      "db_password": Cypress.env('db_password'),
      "db_name": Cypress.env('db_name'),
      "db_prefix": Cypress.env('db_prefix'),
    }

    cy.installJoomla(config)

    cy.doAdministratorLogin(config.username, config.password)
    cy.disableStatistics()
    cy.setErrorReportingToDevelopment()
    cy.doAdministratorLogout()
  })
})
