describe('Test that console command finder', () => {
  it('can index content', () => {
    cy.task('executeCli', { args: ['finder:index'] })
      .its('stdout')
      .should('contain', 'Total Processing Time');
  });
  it('can purge and index content', () => {
    cy.task('executeCli', { args: ['finder:index', 'purge'] })
      .its('stdout')
      .should('contain', 'Clear index')
      .should('contain', 'Total Processing Time');
  });
});
