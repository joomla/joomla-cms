describe('Test in frontend that the content article form', () => {
  afterEach(() => {
    cy.task('queryDB', 'DELETE FROM #__category_item_map WHERE context = \'com_content.article\' AND item_id IN (SELECT id FROM #__content WHERE title IN (\'Test Categories Info Article\', \'Test Main Category Only Article\'))');
    cy.task('queryDB', 'DELETE FROM #__content WHERE title IN (\'test article\', \'Test Categories Info Article\', \'Test Main Category Only Article\')');
    cy.task('queryDB', 'DELETE FROM #__categories WHERE title IN (\'Test Primary Info Category\', \'Test Additional Info Category\')');
    cy.task('queryDB', 'DELETE FROM #__menu WHERE title IN (\'automated test article\', \'Positive Menu Item\', \'Negative Menu Item\')');
  });

  it('can edit an article in menu item', () => {
    cy.db_createMenuItem({ title: 'automated test article', alias: 'automated-test-article', path: 'automated-test-article', link: 'index.php?option=com_content&view=form&layout=edit' })
      .then(() => {
        cy.doFrontendLogin();
        cy.visit('/');
        cy.get('a:contains(automated test article)').click();

        cy.get('#jform_title').type('test article');
        cy.get('[data-submit-task="article.save"]').click();
        cy.get('.success').should('exist');
        cy.get('[type="success"] > .alert-wrapper > .alert-message').should('contain', 'Article submitted.');
      });
  });

  it('can edit an article without menu item', () => {
    cy.doFrontendLogin();
    cy.visit('/index.php?option=com_content&view=form&layout=edit');

    cy.get('#jform_title').type('test article');
    cy.get('[data-submit-task="article.save"]').click();
    cy.get('.success').should('exist');
    cy.get('[type="success"] > .alert-wrapper > .alert-message').should('contain', 'Article submitted.');
  });

  it('shows all categories in the category info', () => {
    cy.db_createCategory({
      title: 'Test Primary Info Category',
      alias: 'test-primary-info-category',
      path: 'test-primary-info-category',
      extension: 'com_content',
    }).then((primaryCategoryId) => cy.db_createCategory({
      title: 'Test Additional Info Category',
      alias: 'test-additional-info-category',
      path: 'test-additional-info-category',
      extension: 'com_content',
    }).then((additionalCategoryId) => {
      cy.db_createArticle({
        title: 'Test Categories Info Article',
        alias: 'test-categories-info-article',
        catid: primaryCategoryId,
      }).then((article) => {
        cy.task('queryDB', `INSERT INTO #__category_item_map (context, item_id, category_id, ordering) VALUES ('com_content.article', ${article.id}, ${additionalCategoryId}, 0)`);

        cy.db_createMenuItem({
          title: 'Positive Menu Item',
          alias: 'positive-menu-item',
          path: 'positive-menu-item',
          link: `index.php?option=com_content&view=article&id=${article.id}`,
          params: JSON.stringify({
            show_category: 1,
            link_category: 0,
          }),
        }).then((positiveMenuId) => {
          cy.visit(`/index.php?option=com_content&view=article&id=${article.id}&Itemid=${positiveMenuId}`);

          cy.get('.article-info .category-name')
            .should('contain.text', 'Categories:')
            .and('contain.text', 'Test Primary Info Category')
            .and('contain.text', 'Test Additional Info Category');
        });
      });
    }));
  });
});
