describe('Test that the users profile view ', () => {
  it('can logout the user without menu item', () => {
    cy.doFrontendLogin();
    cy.visit('index.php?option=com_users&view=login&layout=logout&task=user.menulogout');

    cy.get('#system-message-container').should('contain.text', 'You have been logged out.');
  });

  it('can logout the user in a menu item', () => {
    cy.doFrontendLogin();
    cy.db_createMenuItem({ title: 'Automated logout', link: 'index.php?option=com_users&view=login&layout=logout&task=user.menulogout' });
    cy.visit('/');

    cy.get('a:contains(Automated logout)').click();

    cy.get('#system-message-container').should('contain.text', 'You have been logged out.');
  });
});
