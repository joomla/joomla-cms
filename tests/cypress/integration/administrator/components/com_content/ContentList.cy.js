// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Test com_content List features', () => {
  before(function () {
<<<<<<< HEAD
    cy.doAdministratorLogin(Cypress.env('username'), Cypress.env('password'))
=======
    cy.doAdministratorLogin(this.joomlaconfig.username, this.joomlaconfig.password)
>>>>>>> 91839b1858 (Migrating a bunch of acceptance tests to cypress)
  })

  it('loads without PHP notices and warnings', function () {
    cy.visit('administrator/index.php?option=com_content&view=articles')
    cy.checkForPhpNoticesOrWarnings()
  })

  it('loads without PHP notices and warnings', function () {
    cy.visit('administrator/index.php?option=com_content&view=articles')
    cy.checkForPhpNoticesOrWarnings()
  })

  it('executes CRUD on articles', function () {
    const testArticle = {
      'title': 'Test Article',
      'alias': 'test-article',
      'state': 1
    }

    cy.visit('administrator/index.php?option=com_content&view=articles')
    cy.createArticle(testArticle)
    cy.featureArticle(testArticle.title)
    cy.publishArticle(testArticle.title)
    cy.unPublishArticle(testArticle.title)
    cy.trashArticle(testArticle.title)
    cy.deleteArticle(testArticle.title)
  })
})
