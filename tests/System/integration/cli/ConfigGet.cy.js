describe('Test CLI command config:get', () => {
  it('can get all configuration options', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php config:get`)
      .then((result) => {
        expect(result.stdout).to.contain('Option');
        expect(result.stdout).to.contain('Value');
        expect(result.stdout).to.contain('sitename');
        expect(result.stdout).to.contain('dbtype');
      });
  });

  it('can get database configuration group', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php config:get --group=db`)
      .then((result) => {
        // Check output contains expected database configuration options
        expect(result.stdout).to.contain('Option');
        expect(result.stdout).to.contain('Value');
        expect(result.stdout).to.contain('dbtype');
        expect(result.stdout).to.contain('host');
        expect(result.stdout).to.contain('user');
        expect(result.stdout).to.contain('password');
        expect(result.stdout).to.contain('dbprefix');
        expect(result.stdout).to.contain('db');
        expect(result.stdout).to.contain('dbencryption');
        expect(result.stdout).to.contain('dbsslverifyservercert');
        // Check for specific values
        expect(result.stdout.toLowerCase()).to.contain(Cypress.env('db_type').toLowerCase());
        expect(result.stdout).to.contain('root');
    });
  });

  it('can get mail configuration group', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php config:get --group=mail`)
      .then((result) => {
        // Check output contains expected mail configuration options
        expect(result.stdout).to.contain('Option');
        expect(result.stdout).to.contain('Value');
        expect(result.stdout).to.contain('mailonline');
        expect(result.stdout).to.contain('mailer');
        expect(result.stdout).to.contain('mailfrom');
        expect(result.stdout).to.contain('sendmail');
        expect(result.stdout).to.contain('smtpauth');
        expect(result.stdout).to.contain('smtpuser');
        expect(result.stdout).to.contain('smtppass');
        expect(result.stdout).to.contain('smtphost');
        expect(result.stdout).to.contain('smtpsecure');
        expect(result.stdout).to.contain('smtpport');
    });
  })

  it('can get session configuration group', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php config:get --group=session`)
      .then((result) => {
        // Check output contains expected session configuration options
        expect(result.stdout).to.contain('Option');
        expect(result.stdout).to.contain('Value');
        expect(result.stdout).to.contain('session_handler');
        expect(result.stdout).to.contain('shared_session');
        expect(result.stdout).to.contain('session_metadata');

        // Check for specific values
        expect(result.stdout).to.contain('database');
        expect(result.stdout).to.contain('false');
    });
  });

  it('get error for non existent configuration group', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php config:get --group=test`, { failOnNonZeroExit: false })
      .then((result) => {
        expect(result.stdout).to.contain('[ERROR] Group *test* not found');
    });
  });
});
