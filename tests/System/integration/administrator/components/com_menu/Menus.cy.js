describe('Test that the menu back end list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('administrator/index.php?option=com_menus&view=menus&filter=');
  });

  it('has a title', () => cy.get('h1.page-title').should('contain.text', 'Menus'));

  it('can show a list of menus', () => cy.contains('Main Menu'));

  it('can open the menu form', () => {
    cy.clickToolbarButton('New');

    cy.contains('Menus: Add');
  });
});
