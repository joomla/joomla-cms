describe('Test that console command site', () => {
  it('can set the site down', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php site:down`)
      .its('stdout')
      .should('contain', 'Website is now offline');
  });
  it('can set the site up', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php site:up`)
      .its('stdout')
      .should('contain', 'Website is now online');
  });
});
