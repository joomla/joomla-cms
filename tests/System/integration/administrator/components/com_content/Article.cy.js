describe('Test in backend that the article form', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    // Clear the filter
    cy.visit('/administrator/index.php?option=com_content&filter=');
  });
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__category_item_map WHERE context = 'com_content.article' AND item_id IN (SELECT id FROM #__content WHERE title = 'Test article')");
    cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Test article'");
    cy.task('queryDB', "DELETE FROM #__categories WHERE title IN ('Test Article Primary Category', 'Test Article Additional Category')");
  });

  it('can create an article', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Article saved.');
    cy.contains('Test article');
  });

  it('shows additional categories field', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');

    cy.contains('label', 'Additional Categories').should('exist');
  });

  it('can assign additional categories to an article', () => {
    cy.db_createCategory({
      title: 'Test Article Primary Category',
      alias: 'test-article-primary-category',
      path: 'test-article-primary-category',
      extension: 'com_content',
    }).then((primaryCategoryId) => cy.db_createCategory({
      title: 'Test Article Additional Category',
      alias: 'test-article-additional-category',
      path: 'test-article-additional-category',
      extension: 'com_content',
    }).then((additionalCategoryId) => {
      cy.db_createArticle({ title: 'Test article', catid: primaryCategoryId }).then((article) => {
        cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
        cy.get('#jform_secondary_categories').select(`${additionalCategoryId}`, { force: true });
        cy.clickToolbarButton('Save & Close');

        cy.checkForSystemMessage('Article saved.');
        cy.task('queryDB', `SELECT category_id FROM #__category_item_map WHERE context = 'com_content.article' AND item_id = ${article.id}`)
          .then((rows) => rows.map((row) => Number(row.category_id)))
          .should('include', additionalCategoryId);
      });
    }));
  });

  it('can change access level of a test article', () => {
    cy.db_createArticle({ title: 'Test article' }).then((article) => {
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
      cy.get('#jform_access').select('Special');
      cy.clickToolbarButton('Save & Close');

      cy.get('td').contains('Special').should('exist');
    });
  });

  it('check redirection to list view', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.intercept('index.php?option=com_content&view=articles').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
  });
});
