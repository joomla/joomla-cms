describe('Install Joomla', () => {
  it('Install Joomla', () => {
    cy.exec('rm configuration.php', { failOnNonZeroExit: false });

    const config = {
      sitename: Cypress.env('sitename'),
      name: Cypress.env('name'),
      username: Cypress.env('username'),
      password: Cypress.env('password'),
      email: Cypress.env('email'),
      db_type: Cypress.env('db_type'),
      db_host: Cypress.env('db_host'),
      db_user: Cypress.env('db_user'),
      db_password: Cypress.env('db_password'),
      db_name: Cypress.env('db_name'),
      db_prefix: Cypress.env('db_prefix'),
    };

    cy.installJoomla(config);

    cy.doAdministratorLogin(config.username, config.password, false);

    cy.visit('administrator/index.php?option=com_config&debug=1').then(() => {
      cy.screenshot('debugging-debug-com_config').then(() => {
        throw new Error('testing test exit here');
      });
    });

    cy.disableStatistics();
    cy.setErrorReportingToDevelopment();
    cy.doAdministratorLogout();

    cy.readFile(`${Cypress.env('cmsPath')}/configuration.php`).then((fileContent) => {
      // Update to the correct secret for the API tests because of the bearer token
      let content = fileContent.replace(/^.*\$secret.*$/mg, "public $secret = 'tEstValue';");

      // Setup mailing
      content = content.replace(/^.*\$mailonline.*$/mg, 'public $mailonline = true;');
      content = content.replace(/^.*\$mailer.*$/mg, 'public $mailer = \'smtp\';');
      content = content.replace(/^.*\$smtphost.*$/mg, `public $smtphost = '${Cypress.env('smtp_host')}';`);
      content = content.replace(/^.*\$smtpport.*$/mg, `public $smtpport = '${Cypress.env('smtp_port')}';`);

      // Write the modified content back to the configuration file
      cy.task('writeFile', { path: 'configuration.php', content });
    });
  });
});
