describe('Test that console command finder', () => {
  it('can index content', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php finder:index`)
      .its('stdout')
      .should('contain', 'Total Processing Time');
  });
  it('can purge and index content', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php finder:index purge`)
      .its('stdout')
      .should('contain', 'Clear index')
      .should('contain', 'Total Processing Time');
  });
});
