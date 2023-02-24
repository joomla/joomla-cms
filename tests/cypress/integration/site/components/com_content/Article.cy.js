// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Test view article on front page', () => {
  it('views the front page', function () {
    cy.createDBArticle('test article').then(article =>{

        cy.visit('/')

        cy.contains('test article')
    })
  })
})
