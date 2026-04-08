describe('Test that console command cache', () => {
  it('can clean cache', () => {
    const cachedFile = Cypress.env('cmsPath') + 'administrator/cache/test.txt';
    cy.task('writeRelativeFile', { path: 'administrator/cache/test.txt', content: 'test delete file from cache', mode: 0o666 });
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php cache:clean`)
      .its('stdout')
      .should('contain', 'Cache cleaned');
    cy.readFile(cachedFile).should('not.exist');
  });
});
