describe('Test that content API endpoint', () => {
  afterEach(() => {
    cy.task('queryDB', 'DELETE FROM #__content');
    cy.db_deleteCategory({ title: 'automated test content category' });
  });

  it('can deliver a list of articles', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then(() => cy.api_get('/content/articles'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test article'));
  });

  it('can deliver a list of articles filtered by the modified start and end filter', () => {
    cy.db_createArticle({ title: 'automated test article before', modified: '2025-03-15 10:00:00' })
      .then(() => cy.db_createArticle({ title: 'automated test article within', modified: '2025-03-15 15:00:00' }))
      .then(() => cy.db_createArticle({ title: 'automated test article after', modified: '2025-03-15 20:00:00' }))
      .then(() => cy.api_get('/content/articles?filter[modified_start]=2025-03-15 14:00:00&filter[modified_end]=2025-03-15 16:00:00'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test article within')
        .should('not.include', 'automated test article before')
        .should('not.include', 'automated test article after'));
  });

  it('can deliver a list of unpublished articles', () => {
    cy.db_createArticle({ title: 'automated test article', state: 0 })
      .then(() => cy.api_get('/content/articles?filter[state]=0'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test article'));
  });

  it('can deliver a list of published articles', () => {
    cy.db_createArticle({ title: 'automated test article', state: 1 })
      .then(() => cy.api_get('/content/articles?filter[state]=1'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test article'));
  });

  it('can deliver a single article', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then((article) => cy.api_get(`/content/articles/${article.id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test article'));
  });

  it('can deliver a single article with separate fields intro and full text ', () => {
    cy.db_createArticle({ title: 'automated test article', introtext: 'This is the intro text.', fulltext: 'This is the full text.' })
      .then((article) => cy.api_get(`/content/articles/${article.id}`))
      .then((response) =>
        cy.wrap(response.body.data.attributes).should((attrs) => {
          expect(attrs.introtext).to.include('This is the intro text.');
          expect(attrs.fulltext).to.include('This is the full text.');
        }),
      );
  });

  it('can create an article', () => {
    let categoryId;
    let articleId;

    cy.db_createCategory({ extension: 'com_content' })
      .then((id) => {
        categoryId = id;
        return cy.api_post('/content/articles', {
          title: 'automated test article',
          alias: 'test-article',
          catid: categoryId,
          introtext: '',
          fulltext: '',
          state: 1,
          access: 1,
          language: '*',
          created: '2023-01-01 20:00:00',
          modified: '2023-01-01 20:00:00',
          images: '',
          urls: '',
          attribs: '',
          metadesc: '',
          metadata: '',
        });
      })
      .then((response) => {
        articleId = response.body.data.id; // Capture the article ID
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', 'automated test article');
      })
      .then(() => {
      // Cleanup: Delete the article first (trash then delete)
        if (articleId) {
          cy.api_patch(`/content/articles/${articleId}`, { state: -2 });
          cy.api_delete(`/content/articles/${articleId}`);
        }
        // Cleanup: Delete the category (trash then delete)
        if (categoryId) {
          cy.api_patch(`/content/categories/${categoryId}`, { published: -2 });
          cy.api_delete(`/content/categories/${categoryId}`);
        }
      });
  });

  it('can update an article', () => {
    let articleId;

    cy.db_createArticle({ title: 'automated test article' })
      .then((article) => {
        articleId = article.id;
        return cy.api_patch(`/content/articles/${articleId}`, {
          title: 'updated automated test article',
        });
      })
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', 'updated automated test article');
      })
      .then(() => {
      // Cleanup: Trash and delete the article
        if (articleId) {
          cy.api_patch(`/content/articles/${articleId}`, { state: -2 });
          cy.api_delete(`/content/articles/${articleId}`);
        }
      });
  });

  it('can delete an article', () => {
    cy.db_createArticle({ title: 'automated test article', state: -2 })
      .then((article) => cy.api_delete(`/content/articles/${article.id}`));
  });

  it('creates unique alias when duplicate article is created', () => {
    let categoryId;
    let articleId1;
    let articleId2;

    cy.db_createCategory({ extension: 'com_content' })
      .then((id) => {
        categoryId = id;
        // Create first article
        return cy.api_post('/content/articles', {
          title: 'test article',
          alias: 'test-article',
          catid: categoryId,
          introtext: '',
          fulltext: '',
          state: 1,
          access: 1,
          language: '*',
          created: '2023-01-01 20:00:00',
          modified: '2023-01-01 20:00:00',
          images: '',
          urls: '',
          attribs: '',
          metadesc: '',
          metadata: '',
        });
      })
      .then((response1) => {
        articleId1 = response1.body.data.id;
        // Create second article with same title and alias
        return cy.api_post('/content/articles', {
          title: 'test article',
          alias: 'test-article',
          catid: categoryId,
          introtext: '',
          fulltext: '',
          state: 1,
          access: 1,
          language: '*',
          created: '2023-01-01 20:00:00',
          modified: '2023-01-01 20:00:00',
          images: '',
          urls: '',
          attribs: '',
          metadesc: '',
          metadata: '',
        });
      })
      .then((response2) => {
        articleId2 = response2.body.data.id;
        cy.wrap(response2).its('body').its('data').its('attributes')
          .its('alias')
          .should('equal', 'test-article-2');
      })
      .then(() => {
      // Cleanup: Trash and delete both articles
        if (articleId1) {
          cy.api_patch(`/content/articles/${articleId1}`, { state: -2 });
          cy.api_delete(`/content/articles/${articleId1}`);
        }
        if (articleId2) {
          cy.api_patch(`/content/articles/${articleId2}`, { state: -2 });
          cy.api_delete(`/content/articles/${articleId2}`);
        }

        // Cleanup: Trash and delete the category
        if (categoryId) {
          cy.api_patch(`/content/categories/${categoryId}`, { published: -2 });
          cy.api_delete(`/content/categories/${categoryId}`);
        }
      });
  });

  it('should handle missing required fields gracefully with a clean JSON error schema', () => {
    cy.api_getBearerToken().then((token) => {
      cy.request({
        method: 'POST',
        url: '/api/index.php/v1/content/articles',
        headers: {
          Authorization: `Bearer ${token}`,
        },
        body: {
          // Flat payload missing mandatory required fields like title and category
          title: 'Missing mandatory required fields',
        },
        failOnStatusCode: false,
      })
        .then((response) => {
          // It should fail gracefully, not crash with a 500 Unhandled PHP Error
          expect(response.status).to.be.equal(400);

          // Validate error schema
          expect(response.body).to.have.property('errors');
          expect(response.body.errors).to.be.an('array');
          expect(response.body.errors[0]).to.have.property('title');
          expect(response.body.errors[0].title).to.include('Category');
        });
    });
  });

  it('can deliver a list of articles conforming to page limit and offset pagination flags', () => {
    // Create 3 distinct articles to verify true middle-offset navigation
    cy.db_createArticle({ title: 'automated test article A' })
      .then(() => cy.db_createArticle({ title: 'automated test article B' }))
      .then(() => cy.db_createArticle({ title: 'automated test article C' }))
      .then(() => {
        // Verify Limit: Request only 2 items out of the 3 available
        return cy.api_get('/content/articles?page[limit]=2&page[offset]=0');
      })
      .then((response) => {
        cy.wrap(response).its('body').its('data').should('have.length', 2);
      })
      .then(() => {
        // Verify Offset: Limit to 1 item, but skip the first one (offset=1)
        return cy.api_get('/content/articles?page[limit]=1&page[offset]=1');
      })
      .then((response) => {
        // It must only return 1 item
        cy.wrap(response).its('body').its('data').should('have.length', 1);

        // Assert that it skipped 'C' (newest) and grabbed 'B' (middle)
        cy.wrap(response).its('body').its('data').its('0').its('attributes').its('title')
          .should('include', 'automated test article B');
      });
  });

  it('should successfully fetch articles and verify their category relationships', () => {
    let categoryId;
    let articleId;

    cy.api_post('/content/categories', { title: 'automated test content category', parent_id: 1, extension: 'com_content' })
      .then((response) => {
        categoryId = response.body.data.id;
        return cy.api_post('/content/articles', {
          title: 'automated test article',
          alias: 'automated-test-article',
          catid: categoryId,
          introtext: '',
          fulltext: '',
          state: 1,
          access: 1,
          language: '*',
          created: '2023-01-01 20:00:00',
          modified: '2023-01-01 20:00:00',
          images: '',
          urls: '',
          attribs: '',
          metadesc: '',
          metadata: '',
        });
      })
      .then((articleResponse) => {
        articleId = articleResponse.body.data.id;
        return cy.api_get(`/content/articles/${articleId}`);
      })
      .then((response) => {
        cy.wrap(response).its('body').its('data').should('be.an', 'object');

        cy.wrap(response).its('body').its('data').its('attributes').its('title')
          .should('include', 'automated test article');

        // Validate the relational linkage matches the created category ID
        cy.wrap(response).its('body').its('data').its('relationships').its('category').its('data').its('id')
          .should('equal', String(categoryId));
      })
      .then(() => {
      // Cleanup: Trash and delete the article first
        if (articleId) {
          cy.api_patch(`/content/articles/${articleId}`, { state: -2 });
          cy.api_delete(`/content/articles/${articleId}`);
        }

        // Cleanup: Trash and delete the category
        if (categoryId) {
          cy.api_patch(`/content/categories/${categoryId}`, { published: -2 });
          cy.api_delete(`/content/categories/${categoryId}`);
        }
      });
  });
});
