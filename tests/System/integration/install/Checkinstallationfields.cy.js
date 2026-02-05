describe('Test installation page password strength validation', () => {
  beforeEach(() => {
    // If exists, delete PHP configuration file to force a new installation
    cy.task('deleteRelativePath', 'configuration.php');
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
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.window().then((win) => {
      win.sessionStorage.clear();
    });
  });
});
