describe('Test in backend that the categories list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_categories&view=categories&extension=com_content&filter=');
  });

  it('has a title', () => {
    cy.contains('h1', 'Categories').should('exist');
  });

  it('can display a list of categories', () => {
    cy.db_createCategory({ title: 'Test category' }).then(() => {
      cy.reload();

      cy.contains('Test category');
    });
  });

  it('can open the category form', () => {
    cy.clickToolbarButton('New');

    cy.contains('New Category');
  });

  it('can publish the test category', () => {
    cy.db_createCategory({ title: 'Test category', published: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test category');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Publish').click();

      cy.checkForSystemMessage('Category published.');
    });
  });

  it('can unpublish the test category', () => {
    cy.db_createCategory({ title: 'Test category', published: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test category');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();

      cy.checkForSystemMessage('Category unpublished.');
    });
  });

  it('can trash the test category', () => {
    cy.db_createCategory({ title: 'Test category' }).then(() => {
      cy.reload();
      cy.searchForItem('Test category');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();

      cy.checkForSystemMessage('Category trashed.');
    });
  });

  it('can delete the test category', () => {
    // The category needs to be created through the form so proper assets are created
    cy.visit('/administrator/index.php?option=com_categories&task=category.add&extension=com_content');
    cy.get('#jform_title').type('Test category');
    cy.get('#jform_published').select('Trashed');
    cy.clickToolbarButton('Save & Close');
    cy.setFilter('published', 'Trashed');
    cy.searchForItem('Test category');
    cy.checkAllResults();
    cy.clickToolbarButton('empty trash');
    cy.clickDialogConfirm(true);

    cy.checkForSystemMessage('Category deleted.');
  });
});
