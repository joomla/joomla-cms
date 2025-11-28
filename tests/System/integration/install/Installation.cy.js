describe('Test installation page password strength validation', () => {
  beforeEach(() => {
    cy.visit('/installation/index.php');
    cy.get('#jform_site_name').clear().type(Cypress.env('sitename'));
  });

  it('blocks admin_password with less than 12 characters', () => {
    cy.get('#step1').click();
    cy.get('#jform_admin_password').clear().type('Short1!');
    cy.get('#jform_admin_password').blur();
    cy.contains('Password doesn\'t meet the site\'s requirements');
  });

  it('allows admin_password with 12 or more characters', () => {
    cy.get('#step1').click();
    cy.get('#jform_admin_password').clear().type('ValidPass123!');
    cy.get('#jform_admin_password').blur();
    cy.get('#jform_admin_password').should('have.value', 'ValidPass123!');
  });

  it('allows spaces in the middle of admin_password', () => {
    cy.get('#step1').click();
    cy.get('#jform_admin_password').clear().type('Valid Pass 123!');
    cy.get('#jform_admin_password').blur();
    cy.get('#jform_admin_password').should('have.value', 'Valid Pass 123!');
  });

  it('trims spaces from beginning and end of admin_password', () => {
    cy.get('#step1').click();
    cy.get('#jform_admin_password').clear().type(' ValidPass123! ');
    cy.get('#jform_admin_password').blur();
    cy.get('#jform_admin_password').should('have.value', 'ValidPass123!');
  });

  it('allows empty db_pass (optional field)', () => {
    cy.get('#step1').click();
    cy.get('#jform_admin_user').clear().type(Cypress.env('name'));
    cy.get('#jform_admin_username').clear().type(Cypress.env('username'));
    cy.get('#jform_admin_password').clear().type('ValidPass123!');
    cy.get('#jform_admin_email').clear().type(Cypress.env('email'));
    cy.get('#step2').click();
    cy.get('#jform_db_pass').clear();
    cy.get('#jform_db_pass').blur();
    cy.get('#jform_db_pass').should('have.value', '');
  });
});

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

    // If exists, delete PHP configuration file to force a new installation
    cy.task('deleteRelativePath', 'configuration.php');
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
