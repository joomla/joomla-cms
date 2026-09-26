describe('Test in frontend that the users logout view', () => {
  it('can log out the user without a menu item', () => {
    // Make sure we are really logged in
    cy.doFrontendLogin(null, null, false);
    cy.visit('/index.php?option=com_users&view=login&layout=logout&task=user.menulogout');

    cy.contains(`Hi ${Cypress.env('name')}`).should('not.exist');
    // This is disabled for now as it looks like cypress has an issue after redirect with the session
    // cy.checkForSystemMessage('You have been logged out.');
  });

  it('can log out the user in a menu item', () => {
    cy.db_createMenuItem({ title: 'Automated logout', link: 'index.php?option=com_users&view=login&layout=logout&task=user.menulogout' })
      .then(() => {
        // Make sure we are really logged in
        cy.doFrontendLogin(null, null, false);
        cy.visit('/');
        cy.get('a:contains(Automated logout)').click();

        cy.contains(`Hi ${Cypress.env('name')}`).should('not.exist');
        cy.checkForSystemMessage('You have been logged out.');
      });
  });
});
