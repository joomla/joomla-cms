describe('Test articles tag filtering with AND and OR logic', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
  });

  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__contentitem_tag_map WHERE type_alias = 'com_content.article'");
    cy.task('queryDB', "DELETE FROM #__content WHERE title LIKE '%automated test'");
    cy.task('queryDB', "DELETE FROM #__tags WHERE title LIKE '%automated test'");
  });

  it('can filter articles with OR logic (any tag)', () => {
    // Create two tags
    cy.db_createTag({ title: 'Tag Alpha automated test' })
      .then((tagAlphaId) => cy.db_createTag({ title: 'Tag Beta automated test' })
        .then((tagBetaId) => {
          // Create three articles with different tag combinations
          // Article 1: has both tags
          cy.db_createArticle({ title: 'Article Both Tags automated test' })
            .then((article) => {
              cy.task('queryDB', `INSERT INTO #__contentitem_tag_map (type_alias, core_content_id, content_item_id, tag_id, type_id) VALUES ('com_content.article', 0, ${article.id}, ${tagAlphaId}, 1)`);
              cy.task('queryDB', `INSERT INTO #__contentitem_tag_map (type_alias, core_content_id, content_item_id, tag_id, type_id) VALUES ('com_content.article', 0, ${article.id}, ${tagBetaId}, 1)`);
            });

          // Article 2: has only Tag Alpha
          cy.db_createArticle({ title: 'Article Only Alpha automated test' })
            .then((article) => {
              cy.task('queryDB', `INSERT INTO #__contentitem_tag_map (type_alias, core_content_id, content_item_id, tag_id, type_id) VALUES ('com_content.article', 0, ${article.id}, ${tagAlphaId}, 1)`);
            });

          // Article 3: has only Tag Beta
          cy.db_createArticle({ title: 'Article Only Beta automated test' })
            .then((article) => {
              cy.task('queryDB', `INSERT INTO #__contentitem_tag_map (type_alias, core_content_id, content_item_id, tag_id, type_id) VALUES ('com_content.article', 0, ${article.id}, ${tagBetaId}, 1)`);
            });

          // Visit articles page and apply OR filter
          cy.visit(`/administrator/index.php?option=com_content&view=articles&filter[tag][]=${tagAlphaId}&filter[tag][]=${tagBetaId}&filter[tag_mode]=any`);

          // All three articles should be visible with OR logic
          cy.contains('Article Both Tags automated test').should('be.visible');
          cy.contains('Article Only Alpha automated test').should('be.visible');
          cy.contains('Article Only Beta automated test').should('be.visible');
        }));
  });

  it('can filter articles with AND logic (all tags)', () => {
    // Create two tags
    cy.db_createTag({ title: 'Tag Alpha automated test' })
      .then((tagAlphaId) => cy.db_createTag({ title: 'Tag Beta automated test' })
        .then((tagBetaId) => {
          // Create three articles with different tag combinations
          // Article 1: has both tags
          cy.db_createArticle({ title: 'Article Both Tags automated test' })
            .then((article) => {
              cy.task('queryDB', `INSERT INTO #__contentitem_tag_map (type_alias, core_content_id, content_item_id, tag_id, type_id) VALUES ('com_content.article', 0, ${article.id}, ${tagAlphaId}, 1)`);
              cy.task('queryDB', `INSERT INTO #__contentitem_tag_map (type_alias, core_content_id, content_item_id, tag_id, type_id) VALUES ('com_content.article', 0, ${article.id}, ${tagBetaId}, 1)`);
            });

          // Article 2: has only Tag Alpha
          cy.db_createArticle({ title: 'Article Only Alpha automated test' })
            .then((article) => {
              cy.task('queryDB', `INSERT INTO #__contentitem_tag_map (type_alias, core_content_id, content_item_id, tag_id, type_id) VALUES ('com_content.article', 0, ${article.id}, ${tagAlphaId}, 1)`);
            });

          // Article 3: has only Tag Beta
          cy.db_createArticle({ title: 'Article Only Beta automated test' })
            .then((article) => {
              cy.task('queryDB', `INSERT INTO #__contentitem_tag_map (type_alias, core_content_id, content_item_id, tag_id, type_id) VALUES ('com_content.article', 0, ${article.id}, ${tagBetaId}, 1)`);
            });

          // Visit articles page and apply AND filter
          cy.visit(`/administrator/index.php?option=com_content&view=articles&filter[tag][]=${tagAlphaId}&filter[tag][]=${tagBetaId}&filter[tag_mode]=all`);

          // Only the article with BOTH tags should be visible with AND logic
          cy.contains('Article Both Tags automated test').should('be.visible');
          cy.contains('Article Only Alpha automated test').should('not.exist');
          cy.contains('Article Only Beta automated test').should('not.exist');
        }));
  });
});
