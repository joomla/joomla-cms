describe('Test that console command config', () => {
  it('can get sitename', () => {
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php config:get sitename`)
      .its('stdout')
      .should('contain', Cypress.expose('sitename'));
  });

  it('can set sitename', () => {
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php config:set sitename="${Cypress.expose('sitename')}"`)
      .its('stdout')
      .should('contain', '[OK] Configuration set');
  });

  it('can not set invalid database name', () => {
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php config:set db=invalid`, { failOnNonZeroExit: false }).then((result) => {
      cy.wrap(result).its('exitCode')
        .should('equal', 4);
      cy.wrap(result).its('stdout')
        .should('contain', '[ERROR] Cannot connect to database');
    });
  });
});
