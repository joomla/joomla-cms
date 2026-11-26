['light', 'dark'].forEach((theme) => {
  describe(`Test that Language installation views have no detectable a11y violations - ${theme} mode`, () => {
    beforeEach(() => {
      cy.doAdministratorLogin();
      cy.setCookie('userColorScheme', theme);
    });

    it('in Language Install view', () => {
      cy.visit('/administrator/index.php?option=com_installer&view=languages');
      cy.runAccessibilityScan(theme, '#content');
    });

    it('in Content Language view', () => {
      cy.visit('/administrator/index.php?option=com_languages&view=languages');
      cy.contains('Filter Options').should('be.visible').click();
      cy.runAccessibilityScan(theme, '#content');
    });

    it('in Manage Languages view', () => {
      cy.visit('/administrator/index.php?option=com_languages&view=installed');
      cy.runAccessibilityScan(theme, '#content');
    });

    it('in Edit Language view', () => {
      cy.visit('/administrator/index.php?option=com_languages&view=language&layout=edit');
      cy.runAccessibilityScan(theme);
    });
  });
});
