import { AST_CATEGORY_TITLE } from '../../../../support/constants.mjs';

describe('Test in backend that the category form', () => {
  beforeEach(() => {
    cy.task('queryDB', `DELETE FROM #__categories WHERE title LIKE '${AST_CATEGORY_TITLE}%'`);
    cy.doAdministratorLogin();
    // Clear the filter
    cy.visit('/administrator/index.php?option=com_categories&extension=com_content&filter=');
  });

  it('can create a category', () => {
    cy.visit('/administrator/index.php?option=com_categories&task=category.add&extension=com_content');
    cy.get('#jform_title').should('exist').type(`${AST_CATEGORY_TITLE} Create`);
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Category saved.');
    cy.contains(`${AST_CATEGORY_TITLE} Create`);
  });

  it('can change access level of a test category', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} Access`, extension: 'com_content', parent_id: 1, published: 1 })
      .then((res) => {
        const id = Number(res.body.data.id);
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
