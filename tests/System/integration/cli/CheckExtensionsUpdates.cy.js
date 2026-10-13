describe('Test that console command check extensions update', () => {
  it('can check update', () => {
    cy.task('executeCli', { args: ['update:extensions:check'] })
      .its('stdout')
      .should('contain', 'There are no updates available');
  });
});
