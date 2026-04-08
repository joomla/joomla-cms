describe('Test that console command cache', () => {
  it('can clean cache', () => {
    // 1. Set parameters (assuming these are custom commands you've defined)
    cy.config_setParameter('cache', 1);
    cy.config_setParameter('cache_handler', 'file');

    // 2. Visit the site
    cy.visit('/');

    // 3. Check for folder and conditionally execute the CLI command
    cy.task('checkFolderExists', 'administrator/cache/com_modules').then((folderExists) => {
      if (folderExists) {
        cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php cache:clean`)
          .its('stdout')
          .should('contain', 'Cache cleaned');
        cy.task('checkFolderExists', 'administrator/cache/com_modules').should('be.false');
      } else {
        cy.log('Folder com_modules not found, skipping cache clean.');
      }
    });
  });
});