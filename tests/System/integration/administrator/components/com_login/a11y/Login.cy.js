['light', 'dark'].forEach((theme) => {
  describe(`Test that login and Home dashboard have no detectable a11y violations - ${theme} mode`, () => {
    beforeEach(() => {
      cy.setCookie('userColorScheme', theme);
    });

    it('when password is visible', () => {
      cy.visit('/administrator');
      cy.get('#mod-login-username').type('username');
      cy.get('#mod-login-password').type('password');
      cy.contains('Show Password').click();
      cy.runAccessibilityScan(theme);
    });

    it('when a required field is missing', () => {
      cy.visit('/administrator');
      cy.get('#mod-login-password').type('password');
      cy.get('#btn-login-submit').click();
      cy.get('#system-message-container').should('be.visible');
      cy.runAccessibilityScan(theme);
    });

    it('when wrong credentials are provided', () => {
      cy.visit('/administrator');
      cy.get('#mod-login-username').type('username');
      cy.get('#mod-login-password').type('password');
      cy.get('#btn-login-submit').click();
      cy.get('#system-message-container').should('be.visible');
      cy.runAccessibilityScan(theme);
    });

    it('in Home dashboard', () => {
      cy.doAdministratorLogin();
      cy.setCookie('userColorScheme', theme);
      cy.visit('/administrator');
      cy.get('body').then(($body) => {
        if ($body.find('.shepherd-button-primary').length > 0) {
          cy.cancelTour();
        }
      });
      cy.runAccessibilityScan(theme);
    });
  });
});
