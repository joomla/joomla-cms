describe('Test that console command scheduler', () => {
  it('can list scheduled tasks', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php scheduler:list`)
      .its('stdout')
      .should('contain', 'Rotate Logs');
  });

  it('can update scheduled task state', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php scheduler:state --id=1 --state=1 -n`)
      .its('stdout')
      .should('contain', 'Task ID 1 enabled.');
  });

  it('cannot update state of non-existent task', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php scheduler:state --id=123 --state=1 -n`, { failOnNonZeroExit: false })
      .its('stdout')
      .should('contain', "Task ID '123' does not exist!");
  });

  it('cannot update to non-existent state for a task', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php scheduler:state --id=1 --state=123 -n`, { failOnNonZeroExit: false })
      .its('stdout')
      .should('contain', 'Invalid state passed!');
  });
});
