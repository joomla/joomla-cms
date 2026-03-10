describe('Test that console command extension', () => {
  it('can disable an extension', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php extension:disable 1`)
      .its('stdout')
      .should('contain', '[OK] Component with ID of 1 com_wrapper disabled');
  });

  it('cannot disable a non existent extension', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php extension:disable 2025`, { failOnNonZeroExit: false })
      .its('stdout')
      .should('contain', '[ERROR] Extension with ID of 2025 not found');
  });

  it('can enable an extension', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php extension:enable 1`)
      .its('stdout')
      .should('contain', '[OK] Component with ID of 1 com_wrapper enabled');
  });

  it('cannot enable a non existent extension', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php extension:enable 2025`, { failOnNonZeroExit: false })
      .its('stdout')
      .should('contain', '[ERROR] Extension with ID of 2025 not found');
  });
});
