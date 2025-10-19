describe('Test in backend that the category form', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    // Clear the filter
    cy.visit('/administrator/index.php?option=com_categories&extension=com_content&filter=');
  });
  afterEach(() => cy.task('queryDB', "DELETE FROM #__categories WHERE title = 'Test category'"));

  it('can create a category', () => {
    cy.visit('/administrator/index.php?option=com_categories&task=category.add&extension=com_content');
    cy.get('#jform_title').should('exist').type('Test category');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Category saved.');
    cy.contains('Test category');
  });

  it('can change access level of a test category', () => {
    cy.db_createCategory({ title: 'Test category' }).then((id) => {
      cy.visit(`/administrator/index.php?option=com_categories&task=category.edit&id=${id}&extension=com_content`);
      cy.get('#jform_access').select('Special');
      cy.clickToolbarButton('Save & Close');

      cy.get('td').contains('Special').should('exist');
    });
  });

  it('check redirection to list view', () => {
    cy.visit('/administrator/index.php?option=com_categories&task=category.add&extension=com_content&filter[published]=');
    cy.intercept('index.php?option=com_categories&view=categories&extension=com_content').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
  });
});
