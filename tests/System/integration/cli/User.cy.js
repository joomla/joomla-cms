describe('Test that console command user', () => {
  it('can list users', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php user:list`)
      .its('stdout')
      .should('contain', `${Cypress.env('username')}`);
  });
  it('can add a user', () => {
    const para = '--username=test --name=test --password=123456789012 --email=test@530.test --usergroup=Manager -n';
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php user:add ${para}`)
      .its('stdout')
      .should('contain', 'User created!');
  });
  it('can reset password', () => {
    const para = '--username=test --password=abcdefghilmno -n';
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php user:reset-password ${para}`)
      .its('stdout')
      .should('contain', 'Password changed!');
  });
  it('can add a user to user group', () => {
    const para = '--username=test --group=Registered -n';
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php user:addtogroup ${para}`)
      .its('stdout')
      .should('contain', "Added 'test' to group 'Registered'!");
  });
  it('can remove a user from user group', () => {
    const para = '--username=test --group=Registered -n';
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php user:removefromgroup ${para}`)
      .its('stdout')
      .should('contain', "Removed 'test' from group 'Registered'!");
  });
  it('can delete a user', () => {
    const para = '--username=test -n';
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php user:delete ${para}`)
      .its('stdout')
      .should('contain', 'User test deleted!');
  });
});
