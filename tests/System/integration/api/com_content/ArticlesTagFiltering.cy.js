describe('Test that content API endpoint filters by tags', () => {
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

          // Test OR logic: filter by both tags with mode 'any'
          cy.api_get(`/content/articles?filter[tag][]=${tagAlphaId}&filter[tag][]=${tagBetaId}&filter[tag_mode]=any`)
            .then((response) => {
              const articles = response.body.data;
              const titles = articles.map((article) => article.attributes.title);

              // All three articles should be returned with OR logic
              expect(titles).to.include('Article Both Tags automated test');
              expect(titles).to.include('Article Only Alpha automated test');
              expect(titles).to.include('Article Only Beta automated test');
              expect(articles).to.have.length(3);
            });
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

          // Test AND logic: filter by both tags with mode 'all'
          cy.api_get(`/content/articles?filter[tag][]=${tagAlphaId}&filter[tag][]=${tagBetaId}&filter[tag_mode]=all`)
            .then((response) => {
              const articles = response.body.data;
              const titles = articles.map((article) => article.attributes.title);

              // Only the article with BOTH tags should be returned with AND logic
              expect(titles).to.include('Article Both Tags automated test');
              expect(titles).to.not.include('Article Only Alpha automated test');
              expect(titles).to.not.include('Article Only Beta automated test');
              expect(articles).to.have.length(1);
            });
        }));
  });
});
