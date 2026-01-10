import { AST_CATEGORY_TITLE } from '../../../../support/constants.mjs';

const ADMIN_CATEGORIES_URL = '/administrator/index.php?option=com_categories&view=categories&extension=com_content&filter=';

describe('Test in backend that the categories list', () => {
  beforeEach(() => {
    cy.task('queryDB', `DELETE FROM #__categories WHERE title LIKE '${AST_CATEGORY_TITLE}%'`);
    cy.doAdministratorLogin();
    cy.visit(ADMIN_CATEGORIES_URL);
  });

  it('has a title', () => {
    cy.contains('h1', 'Categories').should('exist');
  });

  it('can display a list of categories', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} Admin`, extension: 'com_content', parent_id: 1, published: 1 }).then(() => {
      cy.visit(ADMIN_CATEGORIES_URL);
      cy.contains(`${AST_CATEGORY_TITLE} Admin`);
    });
  });

  it('can open the category form', () => {
    cy.clickToolbarButton('New');

    cy.contains('New Category');
  });

  it('can publish the test category', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} Admin Publish`, extension: 'com_content', parent_id: 1, published: 0 }).then(() => {
      cy.visit(ADMIN_CATEGORIES_URL);
      cy.searchForItem(`${AST_CATEGORY_TITLE} Admin Publish`);
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Publish').click();

      cy.checkForSystemMessage('Category published.');
    });
  });

  it('can unpublish the test category', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} Admin Unpublish`, extension: 'com_content', parent_id: 1, published: 1 }).then(() => {
      cy.visit(ADMIN_CATEGORIES_URL);
      cy.searchForItem(`${AST_CATEGORY_TITLE} Admin Unpublish`);
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();

      cy.checkForSystemMessage('Category unpublished.');
    });
  });

  it('can trash the test category', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} Admin Trash`, extension: 'com_content', parent_id: 1, published: 1 }).then(() => {
      cy.visit(ADMIN_CATEGORIES_URL);
      cy.searchForItem(`${AST_CATEGORY_TITLE} Admin Trash`);
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();

      cy.checkForSystemMessage('Category trashed.');
    });
  });

  it('can delete the test category', () => {
    // The category needs to be created through the form so proper assets are created
    cy.visit('/administrator/index.php?option=com_categories&task=category.add&extension=com_content');
    cy.get('#jform_title').type(`${AST_CATEGORY_TITLE} Delete`);
    cy.get('#jform_published').select('Trashed');
    cy.clickToolbarButton('Save & Close');
    cy.setFilter('published', 'Trashed');
    cy.searchForItem(`${AST_CATEGORY_TITLE} Delete`);
    cy.checkAllResults();
    cy.clickToolbarButton('empty trash');
    cy.clickDialogConfirm(true);

    cy.checkForSystemMessage('Category deleted.');
  });
});
