describe('Test that console command session', () => {
  it('can garbage collection', () => {
    cy.task('executeCli', { args: ['session:gc'] })
      .its('stdout')
      .should('contain', 'Garbage collection completed');
  });
  it('can metadata garbage collection', () => {
    cy.task('executeCli', { args: ['session:metadata:gc'] })
      .its('stdout')
      .should('contain', 'Metadata garbage collection completed');
  });
});
