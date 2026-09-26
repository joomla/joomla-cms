describe('Test in backend that the templates list', () => {
  beforeEach(() => cy.doAdministratorLogin());

  it('can list site templates', () => {
    cy.visit('/administrator/index.php?option=com_templates&view=templates&client_id=0');
    cy.get('h1.page-title').should('contain.text', 'Templates: Templates (Site)');
    cy.get('#client_id option:selected').should('have.text', 'Site');
    cy.get('#templateList tbody .template-name a').contains('Cassiopeia').click();
    cy.get('h1.page-title').should('contain.text', 'Templates: Customise (Cassiopeia)');
    cy.clickToolbarButton('Cancel');
  });

  it('can list administrator templates', () => {
    cy.visit('/administrator/index.php?option=com_templates&view=templates&client_id=1');
    cy.get('h1.page-title').should('contain.text', 'Templates: Templates (Administrator)');
    cy.get('#client_id option:selected').should('have.text', 'Administrator');
    cy.get('#templateList tbody .template-name a').contains('Atum').click();
    cy.get('h1.page-title').should('contain.text', 'Templates: Customise (Atum)');
    cy.clickToolbarButton('Cancel');
  });

  it('can select client', () => {
    cy.visit('/administrator/index.php?option=com_templates&view=templates&client_id=0');
    cy.get('h1.page-title').should('contain.text', 'Templates: Templates (Site)');
    cy.get('#client_id').select('Administrator');
    cy.get('h1.page-title').should('contain.text', 'Templates: Templates (Administrator)');
    cy.get('#client_id').select('Site');
    cy.get('h1.page-title').should('contain.text', 'Templates: Templates (Site)');
  });

  it('can open styles', () => {
    cy.visit('/administrator/index.php?option=com_templates&view=templates&client_id=0');
    cy.get('h1.page-title').should('contain.text', 'Templates: Templates (Site)');
    cy.get('#toolbar-styles').click();
    cy.get('h1.page-title').should('contain.text', 'Templates: Styles (Site)');
  });

  it('can open options', () => {
    cy.visit('/administrator/index.php?option=com_templates&view=templates&client_id=1');
    cy.intercept('**/administrator/index.php?option=com_config&view=component&component=com_templates*').as('options');
    cy.intercept('**/administrator/index.php?option=com_templates&view=templates&client_id=1*').as('listview');

    cy.clickToolbarButton('Options');
    cy.wait('@options');
    cy.title().should('contain', 'Template: Options');
    cy.get('h1.page-title').should('contain', 'Template: Options');

    cy.clickToolbarButton('Cancel');
    cy.wait('@listview');
  });
});
