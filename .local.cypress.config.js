const { defineConfig } = require('cypress')

module.exports = defineConfig({
  fixturesFolder: 'tests/cypress/fixtures',
  videosFolder: 'tests/cypress/output/videos',
  screenshotsFolder: 'tests/cypress/output/screenshots',
  e2e: {
    setupNodeEvents(on, config) {},
    baseUrl: 'http://itrdev10.verlauf.at',
    specPattern: 'tests/cypress/integration/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'tests/cypress/support/index.js',
    scrollBehavior: 'center',
    browser: 'firefox',
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
    db_host: '172.22.14.61',
    db_name: 'itrdev1_cypress',
    db_user: 'itrdev-itrdev1',
    db_password: 'cNDr8QVjkB',
    db_prefix: 'jos_',
  },
})
