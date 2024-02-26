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

    cy.get('#system-message-container').contains('Checked for updates.').should('exist');
  });

  it('Receives error fetching available updates with invalid metadata', () => {
    cy.db_setInvalidTufRoot();

    cy.visit('/administrator/index.php?option=com_joomlaupdate');

    cy.get('#confirmButton').click();

    cy.get('#system-message-container').contains('Update not possible because the offered update does not have enough signatures.').should('exist');
  });
});
