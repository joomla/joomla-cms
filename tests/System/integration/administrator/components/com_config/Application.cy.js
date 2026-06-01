describe('Test in backend that the application configuration', () => {
  beforeEach(() => {
    cy.task('clearEmails');
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_config');
  });

  it('has a title', () => {
    cy.contains('h1', 'Global Configuration').should('exist');
  });

  it('can display the configuration', () => {
    cy.contains('label', 'Site Name').should('exist');
  });

  it('can send a test mail', () => {
    cy.get('#configTabs div[role="tablist"] button[aria-controls="page-server"]').click();
    cy.get('#jform_mailer').select('smtp');
    cy.get('#jform_smtphost').clear().type(Cypress.expose('smtp_host'));
    cy.get('#jform_smtpport').clear().type(Cypress.expose('smtp_port'));
    cy.get('#jform_smtpsecure').select('none');
    cy.get('#sendtestmail').click();

    cy.task('getMails').then((mails) => {
      cy.checkForSystemMessage('The email was sent to');

      expect(mails.length).to.equal(1);
      cy.wrap(mails[0].body).should('have.string', 'This is a test mail sent using');
      cy.wrap(mails[0].sender).should('equal', Cypress.expose('email'));
      cy.wrap(mails[0].receivers).should('have.property', Cypress.expose('email'));
    });
  });
});
