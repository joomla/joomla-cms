describe('Test the edit view ', () => {
  it('Can display the edit profile form', () => {
    cy.doFrontendLogin();
    cy.visit('http://localhost/joomlanew/index.php?option=com_users&view=profile&layout=edit');
    cy.get('#member-profile > :nth-child(1) > legend').should('contain.text', 'Edit Your Profile');
  });

  it('testing edit for test user through menu item', () => {
    cy.doFrontendLogin();
    cy.db_createMenuItem({ title: 'Automated test edit', link: 'index.php?option=com_users&view=profile&layout=edit' });
        cy.visit('http://localhost/joomlanew');

        cy.get('a:contains(Automated test edit)').click();
        cy.get('#member-profile > :nth-child(1) > legend').should('contain.text', 'Edit Your Profile');
  });
})