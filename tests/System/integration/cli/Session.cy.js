describe('Test that console command session', () => {
  it('can garbage collection', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php session:gc`)
      .its('stdout')
      .should('contain', 'Garbage collection completed');
  });
  it('can metadata garbage collection', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php session:metadata:gc`)
      .its('stdout')
      .should('contain', 'Metadata garbage collection completed');
  });
});
