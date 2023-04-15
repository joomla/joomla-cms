describe('Test that the users profile view ', () => {
  it('can display a edit user profile form without a menu item', () => {
    cy.doFrontendLogin();
    cy.visit('index.php?option=com_users&view=profile&layout=edit');

    cy.get('#member-profile > :nth-child(1) > legend').should('contain.text', 'Edit Your Profile');
  });

  it('can display a edit user profile form in a menu item', () => {
    cy.db_createMenuItem({ title: 'Automated test edit', link: 'index.php?option=com_users&view=profile&layout=edit' })
      .then(() => {
        cy.doFrontendLogin();
        cy.visit('http://localhost/joomlanew');
        cy.get('a:contains(Automated test edit)').click();

        cy.get('#member-profile > :nth-child(1) > legend').should('contain.text', 'Edit Your Profile');
      });
  });
});
