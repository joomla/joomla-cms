describe('Test that console command check extensions update', () => {
  it('can check update', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php update:extensions:check`)
      .its('stdout')
      .should('contain', 'There are no updates available');
  });
});
