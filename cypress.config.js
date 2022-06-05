const { defineConfig } = require('cypress')

module.exports = defineConfig({
  fixturesFolder: 'tests/cypress/fixtures',
  videosFolder: 'tests/cypress/videos',
  screenshotsFolder: 'tests/cypress/screenshots',
  e2e: {
    setupNodeEvents(on, config) {},
    baseUrl: 'http://cypress.test',
    specPattern: 'tests/cypress/integration/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'tests/cypress/support/index.js',
    scrollBehavior: 'center'
  },
  env: {
    sitename: 'Joomla CMS Test',
    username: 'jane doe',
    email: 'admin@example.com',
    user: 'ci-admin',
    pass: 'joomla-17082005',
    db_type: 'MySQLi',
    db_host: 'localhost',
    db_name: 'test_joomla',
    db_user: 'root',
    db_pass: '',
    db_prefix: 'jos_',
  },
})
