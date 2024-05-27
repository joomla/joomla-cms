describe('Test in backend that the Actionlogs', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'User Actions Log');
  });

  it('has no results', () => {
    cy.task('queryDB', 'TRUNCATE #__action_logs');
    cy.reload();
    cy.get('div.alert.alert-info').should('contain.text', 'No Matching Results');
  });

  it('can display a list of actions', () => {
    cy.doAdministratorLogout();
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
    cy.contains('User ci-admin logged in to admin');
    cy.task('queryDB', 'TRUNCATE #__action_logs');
  });

  it('has export button', () => {
    cy.get('#toolbar-download1').click();
    cy.get('#system-message-container').contains('There are no User Action logs to export').should('exist');

  });

  it('can clear logs', () => {
    cy.get('#toolbar-delete1').click();
    cy.on('window:confirm', () => true);
    cy.get('#system-message-container').contains('All User Action logs have been deleted').should('exist');
  });

  it('can delete selected logs', () => {
    cy.get('#toolbar-delete').click();
    cy.on('window:confirm', () => true);
    cy.get('#system-message-container').contains('Please first make a selection from the list').should('exist');
    cy.log('Make a selection first');
    cy.doAdministratorLogout();
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_actionlogs&view=actionlogs');
    cy.checkAllResults();
    cy.get('#toolbar-delete').click();
    cy.on('window:confirm', () => true);
    cy.get('#system-message-container').contains('logs deleted').should('exist');
    cy.task('queryDB', 'TRUNCATE #__action_logs');
  });

});
