describe('Test the update retrieval logic', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
  });

  afterEach(() => {
    cy.db_setValidTufRoot();
  });

  it('Can fetch available updates with valid metadata', () => {
    cy.db_setValidTufRoot();

    cy.visit('/administrator/index.php?option=com_joomlaupdate');

    cy.get('#toolbar joomla-toolbar-button[task="update.purge"] button').click();

    cy.checkForSystemMessage('Checked for updates.');
  });

  it('Receives error fetching available updates with invalid metadata', () => {
    cy.db_setInvalidTufRoot();

    cy.visit('/administrator/index.php?option=com_joomlaupdate');

    cy.get('#confirmButton').click();

    cy.checkForSystemMessage('Update not possible because the offered update does not have enough signatures.');
  });
});
