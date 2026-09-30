describe('Test that console command scheduler', () => {
  it('can list scheduled tasks', () => {
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php scheduler:list`)
      .its('stdout')
      .should('contain', 'Rotate Logs');
  });

  it('can update scheduled task state', () => {
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php scheduler:state --id=1 --state=1 -n`)
      .its('stdout')
      .should('contain', 'Task ID 1 enabled.');
  });

  it('cannot update state of non-existent task', () => {
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php scheduler:state --id=123 --state=1 -n`, { failOnNonZeroExit: false })
      .its('stdout')
      .should('contain', "Task ID '123' does not exist!");
  });

  it('cannot update to non-existent state for a task', () => {
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php scheduler:state --id=1 --state=123 -n`, { failOnNonZeroExit: false })
      .its('stdout')
      .should('contain', 'Invalid state passed!');
  });

  it('can run a task', () => {
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php scheduler:run --id=1`)
      .its('stdout')
      .should('contain', 'Task#01 \'Rotate Logs\' processed in');
  });

  it('cannot run a non existent task', () => {
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php scheduler:run --id=123`, { failOnNonZeroExit: false })
      .its('stdout')
      .should('contain', 'No matching task found!');
  });

  it('cannot run a not due task', () => {
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php scheduler:run`, { failOnNonZeroExit: false })
      .its('stdout')
      .should('contain', 'No tasks due!');
  });
});
