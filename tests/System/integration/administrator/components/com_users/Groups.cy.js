describe('Test in backend that the user group list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_users&view=groups&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Users: Groups');
  });

  it('can display a list of groups', () => {
    cy.db_createUserGroup({ title: 'Test group' }).then(() => {
      cy.reload();

      cy.contains('Test group');
    });
  });

  it('can open the group form', () => {
    cy.clickToolbarButton('New');

    cy.contains('User Group Details');
  });

  it('can delete the test group', () => {
    cy.db_createUserGroup({ title: 'Test group' }).then(() => {
      cy.searchForItem('Test group');
      cy.checkAllResults();
      cy.clickToolbarButton('Delete');
      cy.clickDialogConfirm(true);

      cy.checkForSystemMessage('User Group deleted.');
    });
  });
});
