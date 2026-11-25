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

  it('shows a meaningful error and keeps the database step on wrong database credentials', () => {
    cy.task('deleteRelativePath', 'configuration.php');
    cy.visit('/installation/index.php');
    cy.get('#jform_site_name').clear().type(Cypress.expose('sitename'));
    cy.get('#step1').click();
    cy.get('#jform_admin_user').clear().type(Cypress.expose('name'));
    cy.get('#jform_admin_username').clear().type(Cypress.expose('username'));
    cy.get('#jform_admin_email').clear().type(Cypress.expose('email'));
    cy.get('#jform_admin_password').clear().type(Cypress.expose('password'));
    cy.get('#step2').click();
    cy.get('#installStep3').should('be.visible');

    cy.get('#jform_db_type').select('mysqli');
    cy.get('#jform_db_host').clear().type(Cypress.expose('db_host'));
    cy.get('#jform_db_user').clear().type(Cypress.expose('db_user'));
    cy.get('#jform_db_pass').clear().type('this-is-not-the-password');
    cy.get('#jform_db_name').clear().type(Cypress.expose('db_name'));
    cy.get('#setupButton').click();

    // The error message from the server is shown and the database step is revealed again
    cy.contains('Could not connect to the database');
    cy.get('#installStep3').should('be.visible');
    cy.get('#jform_db_name').should('have.value', Cypress.expose('db_name'));
  });

  it('handles an unexpected non-JSON server response without leaking its content', () => {
    cy.task('deleteRelativePath', 'configuration.php');
    cy.visit('/installation/index.php');
    cy.get('#jform_site_name').clear().type(Cypress.expose('sitename'));
    cy.get('#step1').click();
    cy.get('#jform_admin_user').clear().type(Cypress.expose('name'));
    cy.get('#jform_admin_username').clear().type(Cypress.expose('username'));
    cy.get('#jform_admin_email').clear().type(Cypress.expose('email'));
    cy.get('#jform_admin_password').clear().type(Cypress.expose('password'));
    cy.get('#step2').click();
    cy.get('#installStep3').should('be.visible');

    // Simulate a server error returning an HTML page instead of the expected JSON
    cy.get('#jform_db_user').clear().type(Cypress.expose('db_user'));
    cy.get('#jform_db_name').clear().type(Cypress.expose('db_name'));

    cy.intercept('POST', /installation\/index\.php\?task=installation\.dbcheck/, {
      statusCode: 503,
      headers: { 'Content-Type': 'text/html' },
      body: '<!DOCTYPE html><html><body>simulated-proxy-error-leaked-content</body></html>',
    }).as('dbcheck');

    cy.get('#setupButton').click();
    cy.wait('@dbcheck');

    cy.contains('unexpected HTTP status code: 503');
    cy.get('#installStep3').should('be.visible');
    cy.contains('simulated-proxy-error-leaked-content').should('not.exist');
  });

  it('keeps the database prefix when navigating back and forth between steps', () => {
    cy.task('deleteRelativePath', 'configuration.php');
    cy.visit('/installation/index.php');
    cy.get('#jform_site_name').clear().type(Cypress.expose('sitename'));
    cy.get('#step1').click();
    cy.get('#jform_admin_user').clear().type(Cypress.expose('name'));
    cy.get('#jform_admin_username').clear().type(Cypress.expose('username'));
    cy.get('#jform_admin_email').clear().type(Cypress.expose('email'));
    cy.get('#jform_admin_password').clear().type(Cypress.expose('password'));
    cy.get('#step2').click();
    cy.get('#installStep3').should('be.visible');

    cy.get('#jform_db_prefix').clear().type('cus_');
    cy.get('.previous-step[data-previous="installStep2"]').click();
    cy.get('#installStep2').should('be.visible');
    cy.get('.previous-step[data-previous="installStep1"]').click();
    cy.get('#installStep1').should('be.visible');
    cy.get('#step1').click();
    cy.get('#jform_admin_user').should('have.value', Cypress.expose('name'));
    cy.get('#step2').click();
    cy.get('#installStep3').should('be.visible');
    cy.get('#jform_db_prefix').should('have.value', 'cus_');
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
