describe('Test in frontend that the articles module', () => {
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__category_item_map WHERE context = 'com_content.article' AND item_id IN (SELECT id FROM #__content WHERE title IN ('automated test article', 'automated test multicategory module article'))");
    cy.task('queryDB', "DELETE FROM #__workflow_associations WHERE extension = 'com_content.article' AND item_id IN (SELECT id FROM #__content WHERE title IN ('automated test article', 'automated test multicategory module article'))");
    cy.task('queryDB', "DELETE FROM #__content WHERE title IN ('automated test article', 'automated test multicategory module article')");
    cy.task('queryDB', "DELETE FROM #__modules_menu WHERE moduleid IN (SELECT id FROM #__modules WHERE module = 'mod_articles' AND title IN ('test module', 'test multicategory articles module'))");
    cy.task('queryDB', "DELETE FROM #__modules WHERE module = 'mod_articles' AND title IN ('test module', 'test multicategory articles module')");
    cy.task('queryDB', "DELETE FROM #__categories WHERE title IN ('test category', 'Test Module Main Category', 'Test Module Additional Category', 'Test Module Restricted Category')");
  });

  it('can display the title of the article', () => {
    cy.db_createCategory({ extension: 'com_content' })
      .then(async (categoryId) => {
        await cy.db_createArticle({ title: 'automated test article', catid: categoryId });
        await cy.db_createModule({
          module: 'mod_articles',
          params: JSON.stringify({
            catid: categoryId,
            item_title: 1,
            show_introtext: 0,
          }),
        });
      })
      .then(() => {
        cy.visit('/');

        cy.contains('li', 'automated test article');
      });
  });

  it('can display accessible main and additional categories', () => {
    cy.db_createCategory({
      title: 'Test Module Main Category',
      alias: 'test-module-main-category',
      path: 'test-module-main-category',
      extension: 'com_content',
    }).then((mainCategoryId) => cy.db_createCategory({
      title: 'Test Module Additional Category',
      alias: 'test-module-additional-category',
      path: 'test-module-additional-category',
      extension: 'com_content',
    }).then((additionalCategoryId) => cy.db_createCategory({
      title: 'Test Module Restricted Category',
      alias: 'test-module-restricted-category',
      path: 'test-module-restricted-category',
      extension: 'com_content',
      access: 3,
    }).then((restrictedCategoryId) => {
      cy.db_createArticle({
        title: 'automated test multicategory module article',
        alias: 'automated-test-multicategory-module-article',
        catid: mainCategoryId,
      }).then((article) => {
        cy.task('queryDB', `INSERT INTO #__category_item_map (context, item_id, category_id, ordering) VALUES ('com_content.article', ${article.id}, ${additionalCategoryId}, 0)`);
        cy.task('queryDB', `INSERT INTO #__category_item_map (context, item_id, category_id, ordering) VALUES ('com_content.article', ${article.id}, ${restrictedCategoryId}, 1)`);

        cy.db_createModule({
          title: 'test multicategory articles module',
          module: 'mod_articles',
          params: JSON.stringify({
            catid: [mainCategoryId],
            item_title: 1,
            show_introtext: 0,
            show_category: 1,
            show_category_link: 1,
          }),
        });
      });
    }))).then(() => {
      cy.visit('/');

      cy.contains('li', 'automated test multicategory module article')
        .should('contain.text', 'Test Module Main Category')
        .and('contain.text', 'Test Module Additional Category')
        .and('not.contain.text', 'Test Module Restricted Category')
        .within(() => {
          cy.contains('a', 'Test Module Main Category').should('have.attr', 'href');
          cy.contains('a', 'Test Module Additional Category').should('have.attr', 'href');
        });
    });
  });
});
