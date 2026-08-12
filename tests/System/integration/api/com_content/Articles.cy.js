describe('Test that content API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__content'));

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

  it('can create an article', () => {
    cy.db_createCategory({ extension: 'com_content' })
      .then((categoryId) => cy.api_post('/content/articles', {
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
      }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test article'));
  });

  it('can create an article with multiple secondary categories', () => {
    let articleId = 0;
    let secondaryCategoryId1 = 0;
    let secondaryCategoryId2 = 0;

    cy.db_createCategory({
      title: 'api primary category',
      alias: 'api-primary-category-multi',
      path: 'api-primary-category-multi',
      extension: 'com_content',
    })
      .then((primaryCategoryId) => cy.db_createCategory({
        title: 'api secondary category 1',
        alias: 'api-secondary-category-1',
        path: 'api-secondary-category-1',
        extension: 'com_content',
      }).then((categoryId) => {
        secondaryCategoryId1 = categoryId;

        return cy.db_createCategory({
          title: 'api secondary category 2',
          alias: 'api-secondary-category-2',
          path: 'api-secondary-category-2',
          extension: 'com_content',
        });
      }).then((categoryId) => {
        secondaryCategoryId2 = categoryId;

        return cy.api_post('/content/articles', {
          title: 'automated multiple secondary categories article',
          alias: 'automated-multiple-secondary-categories-article',
          catid: primaryCategoryId,
          secondary_categories: [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ],
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
      }))
      .then((response) => {
        articleId = response.body.data.id;

        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ]);

        return cy.api_get(`/content/articles/${articleId}`);
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ]);
      });
  });

  it('can replace secondary categories for an article', () => {
    let articleId = 0;
    let secondaryCategoryId1 = 0;
    let secondaryCategoryId2 = 0;

    cy.db_createCategory({
      title: 'api primary category',
      alias: 'api-primary-category-replace',
      path: 'api-primary-category-replace',
      extension: 'com_content',
    })
      .then((primaryCategoryId) => cy.db_createCategory({
        title: 'api secondary category 1',
        alias: 'api-secondary-category-replace-1',
        path: 'api-secondary-category-replace-1',
        extension: 'com_content',
      }).then((categoryId) => {
        secondaryCategoryId1 = categoryId;

        return cy.db_createCategory({
          title: 'api secondary category 2',
          alias: 'api-secondary-category-replace-2',
          path: 'api-secondary-category-replace-2',
          extension: 'com_content',
        });
      }).then((categoryId) => {
        secondaryCategoryId2 = categoryId;

        return cy.api_post('/content/articles', {
          title: 'replace secondary categories article',
          alias: 'replace-secondary-categories-article',
          catid: primaryCategoryId,
          secondary_categories: [secondaryCategoryId1],
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
      }))
      .then((response) => {
        articleId = response.body.data.id;

        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [secondaryCategoryId1]);

        return cy.api_patch(`/content/articles/${articleId}`, {
          secondary_categories: [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ],
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ]);

        return cy.api_get(`/content/articles/${articleId}`);
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ]);

        return cy.api_patch(`/content/articles/${articleId}`, {
          title: 'updated title',
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ]);

        return cy.api_patch(`/content/articles/${articleId}`, {
          secondary_categories: [secondaryCategoryId2],
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [secondaryCategoryId2]);

        return cy.api_patch(`/content/articles/${articleId}`, {
          secondary_categories: [],
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', []);

        return cy.api_patch(`/content/articles/${articleId}`, {
          title: 'updated again',
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', []);
      });
  });

  it('returns custom fields assigned to secondary categories', () => {
    let primaryCategoryId = 0;
    let secondaryCategoryId = 0;
    let fieldId = 0;
    let articleId = 0;

    cy.db_createCategory({ extension: 'com_content', title: 'Primary Field Cat' })
      .then((categoryId) => {
        primaryCategoryId = categoryId;
        return cy.db_createCategory({ extension: 'com_content', title: 'Secondary Field Cat' });
      })
      .then((categoryId) => {
        secondaryCategoryId = categoryId;

        return cy.db_createField({
          title: 'test article field',
          name: 'test-article-secondary-field',
          type: 'text',
          context: 'com_content.article',
          state: 1,
          access: 1,
        });
      })
      .then((createdFieldId) => {
        fieldId = createdFieldId;

        return cy.task('queryDB', `INSERT INTO #__fields_categories (field_id, category_id) VALUES (${fieldId}, ${secondaryCategoryId})`);
      })
      .then(() => {
        return cy.api_post('/content/articles', {
          title: 'Article with secondary fields',
          alias: 'article-secondary-fields',
          catid: primaryCategoryId,
          secondary_categories: [secondaryCategoryId],
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
          com_fields: {
            'test-article-secondary-field': 'This is article field data!',
          },
        });
      })
      .then((response) => {
        articleId = response.body.data.id;
        cy.wrap(response).its('body.data.attributes')
          .should('have.property', 'test-article-secondary-field', 'This is article field data!');

        return cy.api_get(`/content/articles/${articleId}`);
      })
      .then((response) => {
        cy.wrap(response).its('body.data.attributes')
          .should('have.property', 'test-article-secondary-field', 'This is article field data!');
      });
  });

  it('can update an article', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then((article) => cy.api_patch(`/content/articles/${article.id}`, { title: 'updated automated test article' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test article'));
  });

  it('can delete an article', () => {
    cy.db_createArticle({ title: 'automated test article', state: -2 })
      .then((article) => cy.api_delete(`/content/articles/${article.id}`));
  });

  it('creates unique alias when duplicate article is created', () => {
    cy.db_createCategory({ extension: 'com_content' })
      .then((categoryId) => {
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
        }).then(() => {
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
        });
      })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('alias')
        .should('equal', 'test-article-2'));
  });
});
