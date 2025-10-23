describe('Test that the package installer plugin', () => {
  it('is shown in extension installer', () => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_installer&view=install');
    cy.get('button[role="tab"]:contains(Upload Package File)').click();

    cy.contains('legend', 'Upload & Install Joomla Extension');
  });
});
