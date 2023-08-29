// type definitions for Cypress object "cy"
// <reference types="cypress" />

describe('Test com_users features', () => {
  beforeEach(() => {
    cy.doAdministratorLogin(Cypress.env('username'), Cypress.env('password'))
    cy.user = {
      "username": "testUser",
      "password": "joomla17082005",
      "name": "Test Bot",
      "email": "Testbot@example.com"
    }
  })

  it('disables sending mails', function () {
    cy.visit('administrator/index.php?option=com_config')
    cy.contains('button', 'Server').click()

    cy.log('**Disable sending mails**')

    cy.visit('administrator/index.php?option=com_config')

    cy.contains('.page-title', 'Global Configuration').scrollIntoView()
    cy.get("div[role='tablist'] button[aria-controls='page-server']").click()
    cy.get('#jform_mailonline0').scrollIntoView().click()

    cy.intercept('index.php?option=com_config*').as('config_save')
    cy.clickToolbarButton('save')
    cy.wait('@config_save')
    cy.contains('.page-title', 'Global Configuration').should('exist')
    cy.contains('#system-message-container', 'Configuration saved.').should('exist')

    cy.log('--Disable sending mails--')
  })

  it('creates a new user', function () {
    cy.visit('administrator/index.php?option=com_users&view=users')
    cy.checkForPhpNoticesOrWarnings()
    cy.get('h1.page-title').should('contain.text', 'Users')
    cy.intercept('index.php?option=com_users&view=user*').as('useredit')
    cy.clickToolbarButton('New')
    cy.wait('@useredit')
    cy.checkForPhpNoticesOrWarnings()

    cy.get('#jform_name').clear().type(cy.user.name)
    cy.get('#jform_username').clear().type(cy.user.username)
    cy.get('#jform_email').clear().type(cy.user.email)
    cy.get('#jform_password').clear().type(cy.user.password)
    cy.get('#jform_password2').clear().type(cy.user.password)
    cy.intercept('index.php?option=com_users&view=users').as('userlist')
    cy.clickToolbarButton('Save & Close')
    cy.wait('@userlist')
    cy.get('h1.page-title').should('contain.text', 'Users')
    cy.get('#system-message-container').contains('User saved').should('exist')
    cy.checkForPhpNoticesOrWarnings()
  })

  it('edits a user', function () {
    cy.visit('administrator/index.php?option=com_users&view=users')
    cy.get('h1.page-title').should('contain.text', 'Users')
    cy.intercept('index.php?option=com_users&view=user*').as('usereditview')
    cy.contains('a', cy.user.name).click()
    cy.wait('@usereditview')

    cy.checkForPhpNoticesOrWarnings()
    cy.contains('button', 'Account Details')
    cy.get('#jform_name').clear().type(cy.user.name)
    cy.get('#jform_username').clear().type(cy.user.username)
    cy.get('#jform_password').clear().type(cy.user.password)
    cy.get('#jform_password2').clear().type(cy.user.password)
    cy.get('#jform_email').clear().type(cy.user.email)

    cy.intercept('administrator/index.php?option=com_users&view=user').as('usereditview')
    cy.clickToolbarButton('Save')
    cy.wait('@usereditview')

    cy.get('#system-message-container').contains('User saved.').should('exist')
    cy.checkForPhpNoticesOrWarnings()
  })

  it('deletes a user', function () {
    cy.visit('administrator/index.php?option=com_users&view=users')
    cy.get('h1.page-title').should('contain.text', 'Users')

    cy.searchForItem(cy.user.username)
    cy.checkAllResults()
    cy.clickToolbarButton('Action')

    cy.contains('Delete').click()

    cy.on("window:confirm", (s) => {
      return true;
    });
      
    cy.get('#system-message-container').contains('User deleted.').should('exist')
    cy.checkForPhpNoticesOrWarnings()
  })

  it('enables sending mails', function () {
    cy.visit('administrator/index.php?option=com_config')
    cy.contains('button', 'Server').click()

    cy.log('**Enable sending mails**')

    cy.visit('administrator/index.php?option=com_config')

    cy.contains('.page-title', 'Global Configuration').scrollIntoView()
    cy.get("div[role='tablist'] button[aria-controls='page-server']").click()
    cy.get('#jform_mailonline1').scrollIntoView().click()

    cy.intercept('index.php?option=com_config*').as('config_save')
    cy.clickToolbarButton('save')
    cy.wait('@config_save')
    cy.contains('.page-title', 'Global Configuration').should('exist')
    cy.contains('#system-message-container', 'Configuration saved.').should('exist')

    cy.log('--Enable sending mails--')
  })
})
