import { defineConfig } from 'cypress';
import setupPlugins from './tests/System/plugins/index.mjs';
import failFast from 'cypress-fail-fast/src/plugin.js'; // Corrected import

export default defineConfig({
  fixturesFolder: 'tests/System/fixtures',
  videosFolder: 'tests/System/output/videos',
  screenshotsFolder: 'tests/System/output/screenshots',
  viewportHeight: 1000,
  viewportWidth: 1200,
  e2e: {
    setupNodeEvents(on, config) {
      setupPlugins(on, config);
      failFast(on, config); // Enable the fail-fast plugin
    },
    baseUrl: 'https://localhost/',
    specPattern: [
      'tests/System/integration/install/**/*.cy.{js,jsx,ts,tsx}',
      'tests/System/integration/administrator/**/*.cy.{js,jsx,ts,tsx}',
      'tests/System/integration/site/**/*.cy.{js,jsx,ts,tsx}',
      'tests/System/integration/api/**/*.cy.{js,jsx,ts,tsx}',
      'tests/System/integration/plugins/**/*.cy.{js,jsx,ts,tsx}',
      'tests/System/integration/cli/**/*.cy.{js,jsx,ts,tsx}',
    ],
    supportFile: 'tests/System/support/index.js',
    scrollBehavior: 'center',
    browser: 'firefox',
    screenshotOnRunFailure: true,
    video: false,
  },
  env: {
    failFast: {
      enabled: true, // Enable fail-fast
      stopOnFirstFail: false, // Continue for non-install tests
      stopOnFailInSpecPattern: 'tests/System/integration/install/**/*.cy.{js,jsx,ts,tsx}', // Stop only for install tests
    },
    sitename: 'Joomla CMS Test',
    name: 'jane doe',
    email: 'admin@example.com',
    username: 'ci-admin',
    password: 'joomla-17082005',
    db_type: 'MySQLi',
    db_host: 'localhost',
    db_port: '',
    db_name: 'test_joomla',
    db_user: 'root',
    db_password: '',
    db_prefix: 'jos_',
    smtp_host: 'localhost',
    smtp_port: '1025',
    cmsPath: '.',
    logFile: '',
  },
});
