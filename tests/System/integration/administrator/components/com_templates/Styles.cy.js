describe('Test in backend that the styles list', () => {
  beforeEach(() => cy.doAdministratorLogin());

  it('can list site styles', () => {
    cy.visit('/administrator/index.php?option=com_templates&view=styles&client_id=0');
    cy.get('h1.page-title').should('contain.text', 'Templates: Styles (Site)');
    cy.get('#client_id option:selected').should('have.text', 'Site');
    cy.get('#styleList tbody a').contains('Cassiopeia - Default').click();
    cy.get('h1.page-title').should('contain.text', 'Templates: Edit Style');
    cy.clickToolbarButton('Cancel');
  });

  it('can list administrator styles', () => {
    cy.visit('/administrator/index.php?option=com_templates&view=styles&client_id=1');
    cy.get('h1.page-title').should('contain.text', 'Templates: Styles (Administrator)');
    cy.get('#client_id option:selected').should('have.text', 'Administrator');
    cy.get('#styleList tbody a').contains('Atum - Default').click();
    cy.get('h1.page-title').should('contain.text', 'Templates: Edit Style');
    cy.clickToolbarButton('Cancel');
  });

  it('can select client', () => {
    cy.visit('/administrator/index.php?option=com_templates&view=styles&client_id=0');
    cy.get('h1.page-title').should('contain.text', 'Templates: Styles (Site)');
    cy.get('#client_id').select('Administrator');
    cy.get('h1.page-title').should('contain.text', 'Templates: Styles (Administrator)');
    cy.get('#client_id').select('Site');
    cy.get('h1.page-title').should('contain.text', 'Templates: Styles (Site)');
  });

  it('can open templates', () => {
    cy.visit('/administrator/index.php?option=com_templates&view=styles&client_id=1');
    cy.get('h1.page-title').should('contain.text', 'Templates: Styles (Administrator)');
    cy.get('#toolbar-templates').click();
    cy.get('h1.page-title').should('contain.text', 'Templates: Templates (Administrator)');
  });

  it('can open options', () => {
    cy.visit('/administrator/index.php?option=com_templates&view=styles&client_id=0');
    cy.intercept('**/administrator/index.php?option=com_config&view=component&component=com_templates*').as('options');
    cy.intercept('**/administrator/index.php?option=com_templates&view=styles&client_id=0*').as('listview');

    cy.clickToolbarButton('Options');
    cy.wait('@options');
    cy.title().should('contain', 'Template: Options');
    cy.get('h1.page-title').should('contain', 'Template: Options');

    cy.clickToolbarButton('Cancel');
    cy.wait('@listview');
  });
});
