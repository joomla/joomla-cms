describe('Test that console command scheduler', () => {
  it('can list scheduled tasks', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php scheduler:list`)
      .its('stdout')
      .should('contain', 'Rotate Logs');
  });
});
