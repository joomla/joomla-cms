describe('Test in frontend that the config config view', () => {
  beforeEach(() => cy.doFrontendLogin());

  it('can edit site configuration without menu item', () => {
    cy.visit('/index.php?option=com_config&view=config');
    cy.title().should('equal', 'Home');
    cy.get('#jform_sitename_pagetitles').select('After');
    cy.get('#application-form button[data-submit-task="config.apply"]').click();

    cy.checkForSystemMessage('Configuration saved.');
    cy.title().should('equal', `Home - ${Cypress.env('sitename')}`);
    cy.get('#jform_sitename_pagetitles').select('No');
    cy.get('#application-form button[data-submit-task="config.apply"]').click();

    cy.checkForSystemMessage('Configuration saved.');
    cy.title().should('equal', 'Home');
  });

  it('can edit site configuration with menu item', () => {
    cy.db_createMenuItem({ title: 'automated test site configuration', link: 'index.php?option=com_config&view=config' })
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test site configuration)').click();

        cy.title().should('equal', 'automated test site configuration');
        cy.get('head meta[name=description]').should('not.exist');
        cy.get('#jform_MetaDesc').clear().type('test meta description');
        cy.get('#application-form button[data-submit-task="config.apply"]').click();

        cy.checkForSystemMessage('Configuration saved.');
        cy.get('head meta[name=description]').should('have.attr', 'content').should('contain', 'test meta description');
        cy.get('#jform_MetaDesc').clear();
        cy.get('#application-form button[data-submit-task="config.apply"]').click();

        cy.checkForSystemMessage('Configuration saved.');
        cy.get('head meta[name=description]').should('not.exist');
      });
  });

  it('can toggle inline help options', () => {
    cy.visit('/index.php?option=com_config&view=config');
    cy.get('#jform_access-desc').should('be.not.visible');
    cy.get('button.button-inlinehelp').click();
    cy.get('#jform_access-desc').should('be.visible');
    cy.get('button.button-inlinehelp').click();
    cy.get('#jform_access-desc').should('be.not.visible');
  });
});
