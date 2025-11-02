describe('Test in frontend that the privacy consent view', () => {
  beforeEach(() => {
    cy.db_updateExtensionParameter('allowUserRegistration', '1', 'com_users');
    cy.db_enableExtension('1', 'plg_system_privacyconsent');
  });

  afterEach(() => {
    cy.db_updateExtensionParameter('allowUserRegistration', '0', 'com_users');
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.task('queryDB', 'DELETE FROM #__privacy_consents');
    cy.task('queryDB', "DELETE FROM #__users WHERE username = 'test'");
  });

  it('can display privacy policy checkbox to users already with an account', () => {
    cy.doFrontendLogin();
    cy.visit('/index.php');
    cy.db_createPrivacyConsent();
    cy.get('.alert').contains('By signing up to this website and agreeing to the Privacy Policy you agree to this website storing your information.');
  });

  it('can allow users already with an account to not agree to the privacy policy', () => {
    cy.doFrontendLogin();
    cy.visit('/index.php');
    cy.get('.controls > .btn-primary').click({ force: true });
    cy.get('.alert-message').should('include.text', "Profile could not be saved: Agreement to the site's Privacy Policy is required.");
  });

  it('can allow users already with an account to agree to the privacy policy', () => {
    cy.doFrontendLogin();
    cy.visit('/index.php');
    cy.get('#jform_privacyconsent_privacy0').click();
    cy.get('.controls > .btn-primary').click({ force: true });
    cy.get('.alert-message').should('include.text', 'Profile saved.');
  });

  it('can allow current users who declined privacy request to edit then agree to privacy consent', () => {
    cy.doFrontendLogin();
    cy.visit('/index.php');
    cy.get('#jform_privacyconsent_privacy1').click();
    cy.get('.controls > .btn-primary').click({ force: true });
    cy.get('.alert-message').should('include.text', "Profile could not be saved: Agreement to the site's Privacy Policy is required.");
    cy.get('#jform_privacyconsent_privacy0').click();
    cy.get('.controls > .btn-primary').click({ force: true });
    cy.get('.alert-message').should('include.text', 'Profile saved.');
  });

  it('can display privacy consent on new user registration form', () => {
    cy.visit('/index.php?option=com_users&view=registration');
    cy.get('.alert').should('contain.text', 'By signing up to this website and agreeing to the Privacy Policy you agree to this website storing your information.');
  });

  it('can display privacy consent on new user registration form and have user decline privacy consent', () => {
    cy.visit('/index.php?option=com_users&view=registration');
    cy.get('#jform_name').clear().type('test user');
    cy.get('#jform_username').clear().type('test');
    cy.get('#jform_email1').clear().type('test@example.com');
    cy.get('#jform_password1').clear().type('testtesttest');
    cy.get('#jform_password2').clear().type('testtesttest');
    cy.get('.com-users-registration__register').click();
    cy.get('.alert-message').should('contain.text', "Registration failed: Agreement to the site's Privacy Policy is required.");
  });

  it('can display privacy consent on new user registration form and have user accept privacy consent', () => {
    cy.visit('/index.php?option=com_users&view=registration');
    cy.get('#jform_name').clear().type('test user');
    cy.get('#jform_username').clear().type('test');
    cy.get('#jform_email1').clear().type('test@example.com');
    cy.get('#jform_password1').clear().type('testtesttest');
    cy.get('#jform_password2').clear().type('testtesttest');
    cy.get('#jform_privacyconsent_privacy0').click();
    cy.get('.com-users-registration__register').click();
    cy.get('.alert-message').should('contain.text', 'Your account has been created and a verification link has been sent to the email address you entered. Note that you must verify the account by selecting the verification link when you get the email and then an administrator will activate your account before you can login.');
  });
});
