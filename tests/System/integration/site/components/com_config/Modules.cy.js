describe('Test in frontend that the config modules view', () => {
  beforeEach(() => cy.doFrontendLogin());

  it('can edit a module', () => {
    cy.visit('/');
    cy.get('nav.mod-breadcrumbs__wrapper li.mod-breadcrumbs__here').should('exist');
    cy.get('nav.mod-breadcrumbs__wrapper li.mod-breadcrumbs__divider').should('not.exist');
    cy.get('nav.mod-breadcrumbs__wrapper a.jmodedit').click();

    cy.title().should('equal', 'Module Settings');
    cy.get('#options button.accordion-button').contains('Options').click();
    cy.get('#jform_params_showHere0').click();
    cy.get('#modules-form button[data-submit-task="modules.apply"]').click();

    cy.checkForSystemMessage('Module saved.');
    cy.get('nav.mod-breadcrumbs__wrapper li.mod-breadcrumbs__here').should('not.exist');
    cy.get('nav.mod-breadcrumbs__wrapper li.mod-breadcrumbs__divider').should('exist');
    cy.get('#options button.accordion-button').contains('Options').click();
    cy.get('#jform_params_showHere1').click();
    cy.get('#modules-form button[data-submit-task="modules.save"]').click();

    cy.checkForSystemMessage('Module saved.');
  });
});
