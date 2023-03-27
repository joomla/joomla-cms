describe('Test that the menu back end list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin(Cypress.env('username'), Cypress.env('password'));
    cy.visit('administrator/index.php?option=com_menus&view=menus');
  });

  it('has a title', () => cy.get('h1.page-title').should('contain.text', 'Menus'));

  it('can show a list of menus', () => cy.contains('Main Menu'));

  it('can open the menu form', () => {
    cy.clickToolbarButton('New');

    cy.contains('Menus: Add');
  });
});
