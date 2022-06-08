const { defineConfig } = require('cypress')

module.exports = defineConfig({
  fixturesFolder: 'tests/cypress/fixtures',
  videosFolder: 'tests/cypress/output/videos',
  screenshotsFolder: 'tests/cypress/output/screenshots',
  e2e: {
    setupNodeEvents(on, config) {},
    baseUrl: 'http://localhost',
    specPattern: 'tests/cypress/integration/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'tests/cypress/support/index.js',
    scrollBehavior: 'center',
    browser: 'chrome',
    screenshotOnRunFailure: false,
    video: false
  },
  env: {
    sitename: 'Joomla CMS Test',
    name: 'jane doe',
    email: 'admin@example.com',
    username: 'ci-admin',
    password: 'joomla-17082005',
    db_type: 'MySQLi',
    db_host: 'localhost',
    db_name: 'test_joomla',
    db_user: 'root',
    db_password: '',
    db_prefix: 'jos_',
  },
})
