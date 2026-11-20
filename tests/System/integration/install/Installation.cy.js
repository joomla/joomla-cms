describe('Install Joomla', () => {
  it('blocks admin password with less than 12 characters', () => {
    cy.task('deleteRelativePath', 'configuration.php');
    cy.visit('/installation/index.php');
    cy.get('#jform_site_name').clear().type(Cypress.expose('sitename'));
    cy.get('#step1').click();
    cy.get('#jform_admin_user').clear().type(Cypress.expose('name'));
    cy.get('#jform_admin_username').clear().type(Cypress.expose('username'));
    cy.get('#jform_admin_email').clear().type(Cypress.expose('email'));
    cy.get('#jform_admin_password').clear().type('Short1!');
    cy.get('#jform_admin_password').blur();

    cy.contains('Password doesn\'t meet the site\'s requirements');

    cy.get('#step2').click();

    cy.get('#installStep3').should('not.be.visible');
  });

  it('allows admin password with 12 or more characters', () => {
    cy.task('deleteRelativePath', 'configuration.php');
    cy.visit('/installation/index.php');
    cy.get('#jform_site_name').clear().type(Cypress.expose('sitename'));
    cy.get('#step1').click();
    cy.get('#jform_admin_user').clear().type(Cypress.expose('name'));
    cy.get('#jform_admin_username').clear().type(Cypress.expose('username'));
    cy.get('#jform_admin_email').clear().type(Cypress.expose('email'));
    cy.get('#jform_admin_password').clear().type('ValidPass123!');
    cy.get('#jform_admin_password').blur();

    cy.get('#jform_admin_password').should('have.value', 'ValidPass123!');

    cy.get('#step2').click();

    cy.get('#installStep3').should('be.visible');
  });

  it('blocks admin password with spaces at the beginning', () => {
    cy.task('deleteRelativePath', 'configuration.php');
    cy.visit('/installation/index.php');
    cy.get('#jform_site_name').clear().type(Cypress.expose('sitename'));
    cy.get('#step1').click();
    cy.get('#jform_admin_user').clear().type(Cypress.expose('name'));
    cy.get('#jform_admin_username').clear().type(Cypress.expose('username'));
    cy.get('#jform_admin_email').clear().type(Cypress.expose('email'));
    cy.get('#jform_admin_password').clear().type(' ValidPass123!');
    cy.get('#jform_admin_password').blur();

    cy.contains('Password must not have spaces at the beginning or end');

    cy.get('#step2').click();

    cy.get('#installStep3').should('not.be.visible');
  });

  it('blocks admin password with spaces at the end', () => {
    cy.task('deleteRelativePath', 'configuration.php');
    cy.visit('/installation/index.php');
    cy.get('#jform_site_name').clear().type(Cypress.expose('sitename'));
    cy.get('#step1').click();
    cy.get('#jform_admin_user').clear().type(Cypress.expose('name'));
    cy.get('#jform_admin_username').clear().type(Cypress.expose('username'));
    cy.get('#jform_admin_email').clear().type(Cypress.expose('email'));
    cy.get('#jform_admin_password').clear().type('ValidPass123! ');
    cy.get('#jform_admin_password').blur();

    cy.contains('Password must not have spaces at the beginning or end');

    cy.get('#step2').click();

    cy.get('#installStep3').should('not.be.visible');
  });

  it('allows spaces in the middle of admin_password', () => {
    cy.task('deleteRelativePath', 'configuration.php');
    cy.visit('/installation/index.php');
    cy.get('#jform_site_name').clear().type(Cypress.expose('sitename'));
    cy.get('#step1').click();
    cy.get('#jform_admin_user').clear().type(Cypress.expose('name'));
    cy.get('#jform_admin_username').clear().type(Cypress.expose('username'));
    cy.get('#jform_admin_email').clear().type(Cypress.expose('email'));
    cy.get('#jform_admin_password').clear().type('Valid Pass 123!');
    cy.get('#jform_admin_password').blur();

    cy.get('#jform_admin_password').should('have.value', 'Valid Pass 123!');

    cy.get('#step2').click();

    cy.get('#installStep3').should('be.visible');
  });

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
