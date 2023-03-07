import './commands'
import 'joomla-cypress'

before(function() {
  const {registerCommands} = require('../../../node_modules/joomla-cypress/src/index.js')

  registerCommands()

  Cypress.on('uncaught:exception', (err, runnable) => {
    console.log("err :" + err)
    console.log("runnable :" + runnable)
    return false
  })
})
