describe('Test in backend that the Installer', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_installer&view=install');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Extensions: Install');
  });

  it('can install and uninstall a component from URL tab', () => {
    cy.get('joomla-tab-element#url').should('exist');
    cy.get('joomla-tab-element#url').click({ force: true });
    cy.get('button#installbutton_url').should('contain.text', 'Check & Install');
    cy.get('input#install_url').type('https://github.com/joomla-extensions/patchtester/releases/download/4.4.0/com_patchtester_4.4.0.zip', { force: true }); // Fill in the input field
    cy.get('button#installbutton_url').click({ force: true });
    // Check if the installation was successful
    cy.contains('Installation of the component was successful.');

    // Uninstall the component
    cy.visit('/administrator/index.php?option=com_installer&view=manage');
    cy.searchForItem('Joomla! Patch Tester');
    cy.checkAllResults();
    cy.clickToolbarButton('Action');
    cy.contains('Uninstall').click();
    cy.clickDialogConfirm(true);
    // Check if the uninstallation was successful
    cy.contains('Uninstalling the component was successful');
  });
});
