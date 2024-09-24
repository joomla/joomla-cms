describe('Test in backend that the cache', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_cache&view=cache');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Maintenance: Clear Cache');
  });

  it('can display message', () => {
    cy.get('div.alert.alert-info').should('contain.text', 'Select the Clear Expired Cache button');
  });

  it('can display a list of chached items', () => {
    cy.get('tr.row0').should('contain.text', '_media_version');
  });

  it('can clear expired cache', () => {
    cy.get('#toolbar-delete2').click();
    cy.on('window:confirm', () => true);
    cy.get('#system-message-container').contains('Expired cached items have been cleared').should('exist');
  });

  it('can delete all', () => {
    cy.get('#toolbar-delete1').click();
    cy.get('#system-message-container').contains('All cache group(s) have been cleared').should('exist');
  });
});
