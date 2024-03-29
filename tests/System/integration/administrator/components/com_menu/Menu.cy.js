describe('Test in backend that the user form', () => {
  beforeEach(() => cy.doAdministratorLogin());
  afterEach(() => cy.task('queryDB', "DELETE FROM #__menu_types WHERE menutype = 'test'"));

  it('can create a new menu', () => {
    cy.visit('/administrator/index.php?option=com_menus&task=menu.add');

    cy.get('#jform_title').clear().type('test menu');
    cy.get('#jform_menutype').clear().type('test');
    cy.get('#jform_menudescription').clear().type('test description');
    cy.clickToolbarButton('Save & Close');

    cy.get('#system-message-container').contains('Menu saved').should('exist');
    cy.contains('test menu');
  });
});
