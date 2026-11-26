['light', 'dark'].forEach((theme) => {
  describe(`Test that article list view has no detectable a11y violations - ${theme} mode`, () => {
    beforeEach(() => {
      cy.doAdministratorLogin();
      cy.setCookie('userColorScheme', theme);
    });

    it('in article list view', () => {
      cy.visit('/administrator/index.php?option=com_content&view=articles');
      cy.contains('Filter Options').should('be.visible').click();
      cy.runAccessibilityScan(theme, '#j-main-container');
    });
  });
});
