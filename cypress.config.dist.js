const { defineConfig } = require('cypress');
const mysql = require('mysql');

module.exports = defineConfig({
  fixturesFolder: 'tests/cypress/fixtures',
  videosFolder: 'tests/cypress/output/videos',
  screenshotsFolder: 'tests/cypress/output/screenshots',
  viewportHeight: 1000,
  viewportWidth: 1200,
  e2e: {
    setupNodeEvents(on, config) {
      function queryTestDB(query, config) {
        return new Promise((resolve, reject) => {
          const connection = mysql.createConnection({
            host: config.env.db_host,
            user: config.env.db_user,
            password: config.env.db_password,
            database: config.env.db_name
          });
          connection.connect();

          connection.query(query, (error, results) => !error || !error.errno ? resolve(results) : reject(error));
        });
      }

      on('task', {
        queryDB: (query) => queryTestDB(query.replace('#__', config.env.db_prefix), config)
      });
    },
    baseUrl: 'http://localhost/',
    specPattern: [
      'tests/cypress/integration/install/*.cy.{js,jsx,ts,tsx}',
      'tests/cypress/integration/administrator/**/*.cy.{js,jsx,ts,tsx}',
      'tests/cypress/integration/site/**/*.cy.{js,jsx,ts,tsx}'
    ],
    supportFile: 'tests/cypress/support/index.js',
    scrollBehavior: 'center',
    browser: 'firefox',
    screenshotOnRunFailure: true,
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
