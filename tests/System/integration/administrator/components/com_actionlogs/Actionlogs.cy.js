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
    cy.get('#system-message-container').contains('There are no User Action logs to export').should('exist');
  });

  it('can clear logs', () => {
    cy.get('#toolbar-delete1').click();
    cy.clickDialogConfirm(true);
    cy.get('#system-message-container').contains('All User Action logs have been deleted').should('exist');
  });

  it('can delete selected logs', () => {
    cy.get('#toolbar-delete').click();
    cy.clickDialogConfirm(true);
    cy.get('#system-message-container').contains('Please first make a selection from the list').should('exist');
    cy.log('Make a selection first');
    cy.doAdministratorLogout();
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
    cy.checkAllResults();
    cy.get('#toolbar-delete').click();
    cy.clickDialogConfirm(true);
    cy.get('#system-message-container').contains('logs deleted').should('exist');
    cy.task('queryDB', 'TRUNCATE #__action_logs');
  });
});
