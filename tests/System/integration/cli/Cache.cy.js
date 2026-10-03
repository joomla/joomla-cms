describe('Test that console command cache', () => {
  it('can clean cache', () => {
    const cachedFile = Cypress.expose('cmsPath') + 'administrator/cache/test.txt';
    cy.task('writeRelativeFile', { path: 'administrator/cache/test.txt', content: 'test delete file from cache', mode: 0o666 });
    cy.task('executeCli', { args: ['cache:clean'] })
      .its('stdout')
      .should('contain', 'Cache cleaned');
    cy.readFile(cachedFile).should('not.exist');
  });
});
