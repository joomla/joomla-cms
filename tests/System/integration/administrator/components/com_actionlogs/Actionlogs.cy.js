describe('Test in backend that the action logs', () => {
  beforeEach(() => {
    cy.db_enableExtension('1', 'plg_actionlog_joomla');
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'User Actions Log');
  });

  it('can display no results', () => {
    cy.task('queryDB', 'TRUNCATE #__action_logs');
    cy.reload();
    cy.get('div.alert.alert-info').should('contain.text', 'No Matching Results');
  });

  it('can display a list of actions', () => {
    cy.doAdministratorLogout();
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
    cy.contains(`User ${Cypress.env('username')} logged in to admin`);
    cy.task('queryDB', 'TRUNCATE #__action_logs');
  });

  it('has an export button', () => {
    cy.get('#toolbar-download1').click();
    cy.checkForSystemMessage('There are no User Action logs to export');
  });

  it('can clear logs', () => {
    cy.get('#toolbar-delete1').click();
    cy.clickDialogConfirm(true);
    cy.checkForSystemMessage('All User Action logs have been deleted');
  });

  it('can delete selected logs', () => {
    cy.get('#toolbar-delete').click();
    cy.clickDialogConfirm(true);
    cy.checkForSystemMessage('Please first make a selection from the list');
    cy.log('Make a selection first');
    cy.doAdministratorLogout();
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
    cy.checkAllResults();
    cy.get('#toolbar-delete').click();
    cy.clickDialogConfirm(true);
    cy.checkForSystemMessage('logs deleted');
    cy.task('queryDB', 'TRUNCATE #__action_logs');
  });
});
