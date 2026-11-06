describe('Test that console command site', () => {
  it('can set the site down', () => {
    cy.task('executeCli', { args: ['site:down'] })
      .its('stdout')
      .should('contain', 'Website is now offline');
  });
  it('can set the site up', () => {
    cy.task('executeCli', { args: ['site:up'] })
      .its('stdout')
      .should('contain', 'Website is now online');
  });
});
