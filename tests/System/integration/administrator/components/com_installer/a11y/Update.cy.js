['light', 'dark'].forEach((theme) => {
  describe(`Test that update views have no detectable a11y violations - ${theme} mode`, () => {
    beforeEach(() => {
      cy.doAdministratorLogin();
      cy.setCookie('userColorScheme', theme);
    });

    it('in Joomla Update view', () => {
      cy.visit('/administrator/index.php?option=com_joomlaupdate');
      cy.get('#confirmButton').should('be.visible').click();
      cy.get('joomla-alert').should('be.visible').invoke('attr', 'style', 'animation: none');
      cy.runAccessibilityScan(theme, '#content');
    });

    it('in Extensions Update view', () => {
      cy.visit('/administrator/index.php?option=com_installer&view=update');
      cy.get('#confirmButton').should('be.visible').click();
      cy.get('joomla-alert').should('be.visible').invoke('attr', 'style', 'animation: none');
      cy.runAccessibilityScan(theme, '#content');
    });

    it('in Update Sites view', () => {
      cy.visit('/administrator/index.php?option=com_installer&view=updatesites');
      cy.contains('Filter Options').should('be.visible').click();
      cy.runAccessibilityScan(theme, '#content');
    });

    it('in Update by upload view', () => {
      cy.visit('/administrator/index.php?option=com_joomlaupdate&view=upload');
      cy.runAccessibilityScan(theme, '#content');
    });
  });
});
