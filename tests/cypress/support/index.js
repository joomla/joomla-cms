// ***********************************************************
// This example support/index.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// Import commands.js using ES2015 syntax:
import './commands'

// Import Joomla-Cypress package
const { registerCommands } = require('joomla-cypress')
registerCommands()

// Alternatively you can use CommonJS syntax:
// require('./commands')

before(function() {
  let joomlaconfig = {
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

  this.joomlaconfig = joomlaconfig
})
