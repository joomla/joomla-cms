describe('Test that console command user', () => {
  it('can list users', () => {
    cy.task('executeCli', { args: ['user:list'] })
      .its('stdout')
      .should('contain', `${Cypress.expose('username')}`);
  });
  it('can add a user', () => {
    const para = ['--username=test', '--name=test', '--password=123456789012', '--email=test@530.test', '--usergroup=Manager', '-n'];
    cy.task('executeCli', { args: ['user:add', ...para] })
      .its('stdout')
      .should('contain', 'User created!');
  });
  it('can reset password', () => {
    const para = ['--username=test', '--password=abcdefghilmno', '-n'];
    cy.task('executeCli', { args: ['user:reset-password', ...para] })
      .its('stdout')
      .should('contain', 'Password changed!');
  });
  it('can add a user to user group', () => {
    const para = ['--username=test', '--group=Registered', '-n'];
    cy.task('executeCli', { args: ['user:addtogroup', ...para] })
      .its('stdout')
      .should('contain', "Added 'test' to group 'Registered'!");
  });
  it('can remove a user from user group', () => {
    const para = ['--username=test', '--group=Registered', '-n'];
    cy.task('executeCli', { args: ['user:removefromgroup', ...para] })
      .its('stdout')
      .should('contain', "Removed 'test' from group 'Registered'!");
  });
  it('can delete a user', () => {
    const para = ['--username=test', '-n'];
    cy.task('executeCli', { args: ['user:delete', ...para] })
      .its('stdout')
      .should('contain', 'User test deleted!');
  });
});
