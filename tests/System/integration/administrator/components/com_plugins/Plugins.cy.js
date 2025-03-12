describe('Test in backend that the plugins list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_plugins&view=plugins');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Plugins');
  });

  it('can display a list of plugins', () => {
    cy.contains('Action Log - Joomla');
  });

  it('can unpublish a plugin', () => {
    cy.searchForItem('Action Log - Joomla');
    cy.checkAllResults();
    cy.contains('Disable').click();
    cy.on('window:confirm', () => true);
    cy.checkForSystemMessage('Plugin disabled.');
  });

  it('can publish a plugin', () => {
    cy.searchForItem('Action Log - Joomla');
    cy.checkAllResults();
    cy.contains('Enable').click();
    cy.on('window:confirm', () => true);
    cy.checkForSystemMessage('Plugin enabled.');
  });

  it('can edit a plugin', () => {
    cy.searchForItem('Action Log - Joomla');
    cy.get('a').contains('Action Log - Joomla').click();
    cy.contains('Plugins: Action Log - Joomla');
    cy.contains('Close').click();
  });
});
