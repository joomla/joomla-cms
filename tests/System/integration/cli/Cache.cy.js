describe('Test that console command cache', () => {
  it('can clean cache', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php cache:clean`)
      .its('stdout')
      .should('contain', 'Cache cleaned');
  });
});
