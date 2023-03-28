describe('Test that the categories back end list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('administrator/index.php?option=com_categories&view=categories&extension=com_content&filter=');
  });

  it('has a title', () => {
    cy.contains('h1', 'Categories').should('exist');
  });

  it('can show a list of categories', () => {
    cy.db_createCategory({ title: 'Test category' }).then(() => {
      cy.reload();

      cy.contains('Test category');
    });
  });

  it('can open the category form', () => {
    cy.clickToolbarButton('New');

    cy.contains('New Category');
  });
});
