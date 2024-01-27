describe('Test in backend that the field form', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
  });

  it('Can fetch available updates', () => {
    cy.visit('/administrator/index.php?option=com_joomlaupdate');

    cy.get('#toolbar joomla-toolbar-button[task="update.purge"] button').click()

    cy.get('#system-message-container').contains('Checked for updates.').should('exist');
  });
});
