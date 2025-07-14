describe('Test in frontend that the config config view', () => {
  beforeEach(() => cy.doFrontendLogin());

  it('can edit template settings without menu item', () => {
    cy.visit('/index.php?option=com_config&view=templates');
    cy.title().should('equal', 'Home');
    cy.get('#params_siteDescription').clear().type('test site description');
    cy.get('#templates-form button[data-submit-task="templates.apply"]').click();

    cy.checkForSystemMessage('Configuration saved.');
    cy.get('header div.site-description').should('contain', 'test site description');
    cy.get('#params_siteDescription').clear();
    cy.get('#templates-form button[data-submit-task="templates.apply"]').click();

    cy.checkForSystemMessage('Configuration saved.');
    cy.get('header div.site-description').should('not.exist');
  });

  it('can edit template settings with menu item', () => {
    cy.db_createMenuItem({ title: 'automated test template settings', link: 'index.php?option=com_config&view=templates' })
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test template settings)').click();

        cy.title().should('equal', 'automated test template settings');
        cy.get('#params_brand0').click();
        cy.get('#templates-form button[data-submit-task="templates.apply"]').click();

        cy.checkForSystemMessage('Configuration saved.');
        cy.get('header div.navbar-brand').should('not.exist');
        cy.get('#params_brand1').click();
        cy.get('#templates-form button[data-submit-task="templates.apply"]').click();

        cy.checkForSystemMessage('Configuration saved.');
        cy.get('header div.navbar-brand').should('exist');
      });
  });
});
