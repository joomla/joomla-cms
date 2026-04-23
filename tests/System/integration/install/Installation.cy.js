describe('Install Joomla', () => {
  it('Install Joomla', () => {
    const config = {
      sitename: Cypress.expose('sitename'),
      name: Cypress.expose('name'),
      username: Cypress.expose('username'),
      password: Cypress.expose('password'),
      email: Cypress.expose('email'),
      db_type: Cypress.expose('db_type'),
      db_host: Cypress.expose('db_host'),
      db_port: Cypress.expose('db_port'),
      db_user: Cypress.expose('db_user'),
      db_password: Cypress.expose('db_password'),
      db_name: Cypress.expose('db_name'),
      db_prefix: Cypress.expose('db_prefix'),
    };

    // If exists, delete PHP configuration file to force a new installation
    cy.task('deleteRelativePath', 'configuration.php');
    cy.installJoomla(config);

    // Disable compat plugin
    cy.db_enableExtension(0, 'plg_behaviour_compat6');

    cy.doAdministratorLogin(config.username, config.password, false);
    cy.cancelTour();
    cy.disableStatistics();
    cy.setErrorReportingToDevelopment();
    cy.doAdministratorLogout();

    // Setup mailing
    cy.config_setParameter('mailonline', true);
    cy.config_setParameter('mailer', 'smtp');
    cy.config_setParameter('smtphost', Cypress.expose('smtp_host'));
    cy.config_setParameter('smtpport', Cypress.expose('smtp_port'));
  });
});
