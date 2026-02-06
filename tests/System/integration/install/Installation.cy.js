describe('Install Joomla', () => {
  it('Install Joomla', () => {
    const config = {
      sitename: Cypress.env('sitename'),
      name: Cypress.env('name'),
      username: Cypress.env('username'),
      password: Cypress.env('password'),
      email: Cypress.env('email'),
      db_type: Cypress.env('db_type'),
      db_host: Cypress.env('db_host'),
      db_port: Cypress.env('db_port'),
      db_user: Cypress.env('db_user'),
      db_password: Cypress.env('db_password'),
      db_name: Cypress.env('db_name'),
      db_prefix: Cypress.env('db_prefix'),
    };


    cy.task('db_clean');    
    // If exists, delete PHP configuration file to force a new installation
    cy.task('deleteRelativePath', 'configuration.php');
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.window().then((win) => {
      win.sessionStorage.clear();
      // Forcefully expire the Joomla cookie if it exists
      document.cookie = "joomla_session=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    });
    cy.installJoomla(config);

    // Disable compat plugin
    cy.db_enableExtension(0, 'plg_behaviour_compat');

    cy.doAdministratorLogin(config.username, config.password, false);
    cy.cancelTour();
    cy.disableStatistics();
    cy.setErrorReportingToDevelopment();
    cy.doAdministratorLogout();

    // Setup mailing
    cy.config_setParameter('mailonline', true);
    cy.config_setParameter('mailer', 'smtp');
    cy.config_setParameter('smtphost', Cypress.env('smtp_host'));
    cy.config_setParameter('smtpport', Cypress.env('smtp_port'));
  });
});
