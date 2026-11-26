describe('Test that console command cache', () => {
  it('can clean cache', () => {
    // cache:clean removes the cache group folders, not files in the cache root
    const cachedFile = `${Cypress.expose('cmsPath')}/administrator/cache/cypress-test/test.txt`;
    cy.task('writeRelativeFile', { path: 'administrator/cache/cypress-test/test.txt', content: 'test delete file from cache', mode: 0o666 });
    cy.readFile(cachedFile).should('exist');
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php cache:clean`)
      .its('stdout')
      .should('contain', 'Cache cleaned');
    cy.readFile(cachedFile).should('not.exist');
  });
});
