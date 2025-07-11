describe('Test in frontend that the users profile view edit layout', () => {
  it('can display a user form without a menu item', () => {
    cy.doFrontendLogin();
    cy.visit('/index.php?option=com_users&view=profile&layout=edit');

    cy.get('#member-profile > :nth-child(1) > legend').should('contain.text', 'Edit Your Profile');
  });

  it('can display a user form in a menu item', () => {
    cy.db_createMenuItem({ title: 'Automated test edit', link: 'index.php?option=com_users&view=profile&layout=edit' })
      .then(() => {
        cy.doFrontendLogin();
        cy.visit('/');
        cy.get('a:contains(Automated test edit)').click();

        cy.get('#member-profile > :nth-child(1) > legend').should('contain.text', 'Edit Your Profile');
      });
  });

  it('can edit a test user without a menu item', () => {
    cy.db_createUser({
      name: 'automated test user 2', username: 'automatedtestuser2', password: '098f6bcd4621d373cade4e832627b4f6',
    })
      .then(() => {
        cy.doFrontendLogin('automatedtestuser2', 'test', false);
        cy.visit('/index.php?option=com_users&view=profile&layout=edit');

        cy.get('#jform_name').clear().type('automated test user edited');
        cy.get('#jform_email1').clear().type('testedited@example.com');
        cy.get('.controls > .btn-primary').click({ force: true });

        cy.checkForSystemMessage('Profile saved.');
        cy.get('#users-profile-core').should('contain.text', 'Name');
        cy.get('#users-profile-core').should('contain.text', 'automated test user edited');
      });
  });

  it('can edit a test user in a menu item', () => {
    cy.db_createUser({
      name: 'automated test user', username: 'automatedtestuser', password: '098f6bcd4621d373cade4e832627b4f6',
    });
    cy.db_createMenuItem({ title: 'Automated test edit test user', link: 'index.php?option=com_users&view=profile&layout=edit' })
      .then(() => {
        cy.doFrontendLogin('automatedtestuser', 'test', false);
        cy.visit('/');
        cy.get('a:contains(Automated test edit)').click();

        cy.get('#jform_name').clear().type('automated test user edited');
        cy.get('#jform_email1').clear().type('testedited@example.com');
        cy.get('.controls > .btn-primary').should('be.visible').click({ force: true });

        cy.checkForSystemMessage('Profile saved.');
        cy.get('.profile .btn-primary').should('be.visible').click({ force: true });
        cy.get('#jform_name').should('have.value', 'automated test user edited');
        cy.get('#jform_email1').should('have.value', 'testedited@example.com');
      });
  });
});
