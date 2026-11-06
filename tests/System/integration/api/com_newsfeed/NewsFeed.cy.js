describe('Test that newsfeed API endpoint', () => {
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__contentitem_tag_map WHERE type_alias = 'com_newsfeeds.newsfeed'");
    cy.task('queryDB', "DELETE FROM #__newsfeeds WHERE name LIKE '%automated test feed%' OR name LIKE '%automated test contact%'");
    cy.task('queryDB', "DELETE FROM #__tags WHERE title = 'automated test feed tag'");
  });

  ['joomla.org'].forEach((file) => {
    it(`can deliver a list of feeds from ${file}`, () => {
      cy.db_createNewsFeed({ name: 'automated test feed', link: `${Cypress.config('baseUrl')}/tests/System/data/com_newsfeeds/${file}.xml` })
        .then(() => cy.api_get('/newsfeeds/feeds'))
        .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('name')
          .should('include', 'automated test feed'));
    });

    it('can create a feed', () => {
      cy.db_createCategory({ extension: 'com_newsfeeds' })
        .then((categoryId) => cy.api_post('/newsfeeds/feeds', {
          name: 'automated test feed',
          alias: 'test-feed',
          link: `${Cypress.config('baseUrl')}/tests/System/data/com_newsfeeds/${file}.xml`,
          catid: categoryId,
          published: 1,
          language: '*',
          metadesc: '',
          metakey: '',
          description: '',
          images: {
            float_first: '',
            float_second: '',
            image_first: '',
            image_first_alt: '',
            image_first_caption: '',
            image_second: '',
            image_second_alt: '',
            image_second_caption: '',
          },
          metadata: {
            hits: '',
            rights: '',
            robots: '',
            tags: {
              tags: '',
              typeAlias: null,
            },
          },
          params: {
            feed_character_count: '',
            feed_display_order: '',
            newsfeed_layout: '',
            show_feed_description: '',
            show_feed_image: '',
            show_item_description: '',
          },
        }))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'automated test feed'));
    });

    it('can create a feed with multiple secondary categories', () => {
      let feedId = 0;
      let secondaryCategoryId1 = 0;
      let secondaryCategoryId2 = 0;

      cy.db_createCategory({
        title: 'api primary category',
        alias: 'api-primary-category-multi-feed',
        path: 'api-primary-category-multi-feed',
        extension: 'com_newsfeeds',
      })
        .then((primaryCategoryId) => cy.db_createCategory({
          title: 'api secondary category 1',
          alias: 'api-secondary-category-feed-1',
          path: 'api-secondary-category-feed-1',
          extension: 'com_newsfeeds',
        }).then((categoryId) => {
          secondaryCategoryId1 = categoryId;

          return cy.db_createCategory({
            title: 'api secondary category 2',
            alias: 'api-secondary-category-feed-2',
            path: 'api-secondary-category-feed-2',
            extension: 'com_newsfeeds',
          });
        }).then((categoryId) => {
          secondaryCategoryId2 = categoryId;

          return cy.api_post('/newsfeeds/feeds', {
            name: 'automated test feed multiple secondary categories',
            alias: 'automated-test-feed-multiple-secondary-categories',
            link: `${Cypress.config('baseUrl')}/tests/System/data/com_newsfeeds/${file}.xml`,
            catid: primaryCategoryId,
            secondary_categories: [
              secondaryCategoryId1,
              secondaryCategoryId2,
            ],
            published: 1,
            language: '*',
            metadesc: '',
            metakey: '',
            description: '',
            images: {
              float_first: '',
              float_second: '',
              image_first: '',
              image_first_alt: '',
              image_first_caption: '',
              image_second: '',
              image_second_alt: '',
              image_second_caption: '',
            },
            metadata: {
              hits: '',
              rights: '',
              robots: '',
              tags: {
                tags: '',
                typeAlias: null,
              },
            },
            params: {
              feed_character_count: '',
              feed_display_order: '',
              newsfeed_layout: '',
              show_feed_description: '',
              show_feed_image: '',
              show_item_description: '',
            },
          });
        }))
        .then((response) => {
          feedId = response.body.data.id;

          cy.wrap(response)
            .its('body.data.attributes.secondary_categories')
            .should('deep.equal', [
              secondaryCategoryId1,
              secondaryCategoryId2,
            ]);

          return cy.api_get(`/newsfeeds/feeds/${feedId}`);
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

    it('can replace secondary categories for a feed', () => {
      let feedId = 0;
      let secondaryCategoryId1 = 0;
      let secondaryCategoryId2 = 0;

      cy.db_createCategory({
        title: 'api primary category',
        alias: 'api-primary-category-replace-feed',
        path: 'api-primary-category-replace-feed',
        extension: 'com_newsfeeds',
      })
        .then((primaryCategoryId) => cy.db_createCategory({
          title: 'api secondary category 1',
          alias: 'api-secondary-category-replace-feed-1',
          path: 'api-secondary-category-replace-feed-1',
          extension: 'com_newsfeeds',
        }).then((categoryId) => {
          secondaryCategoryId1 = categoryId;

          return cy.db_createCategory({
            title: 'api secondary category 2',
            alias: 'api-secondary-category-replace-feed-2',
            path: 'api-secondary-category-replace-feed-2',
            extension: 'com_newsfeeds',
          });
        }).then((categoryId) => {
          secondaryCategoryId2 = categoryId;

          return cy.api_post('/newsfeeds/feeds', {
            name: 'automated test feed replace secondary categories',
            alias: 'automated-test-feed-replace-secondary-categories',
            link: `${Cypress.config('baseUrl')}/tests/System/data/com_newsfeeds/${file}.xml`,
            catid: primaryCategoryId,
            secondary_categories: [secondaryCategoryId1],
            published: 1,
            language: '*',
            metadesc: '',
            metakey: '',
            description: '',
            images: {
              float_first: '',
              float_second: '',
              image_first: '',
              image_first_alt: '',
              image_first_caption: '',
              image_second: '',
              image_second_alt: '',
              image_second_caption: '',
            },
            metadata: {
              hits: '',
              rights: '',
              robots: '',
              tags: {
                tags: '',
                typeAlias: null,
              },
            },
            params: {
              feed_character_count: '',
              feed_display_order: '',
              newsfeed_layout: '',
              show_feed_description: '',
              show_feed_image: '',
              show_item_description: '',
            },
          });
        }))
        .then((response) => {
          feedId = response.body.data.id;

          cy.wrap(response)
            .its('body.data.attributes.secondary_categories')
            .should('deep.equal', [secondaryCategoryId1]);

          return cy.api_patch(`/newsfeeds/feeds/${feedId}`, {
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

          return cy.api_get(`/newsfeeds/feeds/${feedId}`);
        })
        .then((response) => {
          cy.wrap(response)
            .its('body.data.attributes.secondary_categories')
            .should('deep.equal', [
              secondaryCategoryId1,
              secondaryCategoryId2,
            ]);

          return cy.api_patch(`/newsfeeds/feeds/${feedId}`, {
            name: 'updated automated test feed',
          });
        })
        .then((response) => {
          cy.wrap(response)
            .its('body.data.attributes.secondary_categories')
            .should('deep.equal', [
              secondaryCategoryId1,
              secondaryCategoryId2,
            ]);

          return cy.api_patch(`/newsfeeds/feeds/${feedId}`, {
            secondary_categories: [secondaryCategoryId2],
          });
        })
        .then((response) => {
          cy.wrap(response)
            .its('body.data.attributes.secondary_categories')
            .should('deep.equal', [secondaryCategoryId2]);

          return cy.api_patch(`/newsfeeds/feeds/${feedId}`, {
            secondary_categories: [],
          });
        })
        .then((response) => {
          cy.wrap(response)
            .its('body.data.attributes.secondary_categories')
            .should('deep.equal', []);
        });
    });

    it('keeps tags when patching a feed without tags', () => {
      let feedId = 0;
      let tagId = 0;

      cy.db_createCategory({ extension: 'com_newsfeeds' })
        .then((categoryId) => cy.db_createTag({
          title: 'automated test feed tag',
          alias: 'automated-test-feed-tag',
          path: 'automated-test-feed-tag',
        }).then((createdTagId) => {
          tagId = createdTagId;

          return cy.api_post('/newsfeeds/feeds', {
            name: 'automated test feed with tag',
            alias: 'automated-test-feed-with-tag',
            link: `${Cypress.config('baseUrl')}/tests/System/data/com_newsfeeds/${file}.xml`,
            catid: categoryId,
            tags: [tagId],
            published: 1,
            language: '*',
            metadesc: '',
            metakey: '',
            description: '',
            images: {
              float_first: '',
              float_second: '',
              image_first: '',
              image_first_alt: '',
              image_first_caption: '',
              image_second: '',
              image_second_alt: '',
              image_second_caption: '',
            },
            metadata: {
              hits: '',
              rights: '',
              robots: '',
              tags: {
                tags: '',
                typeAlias: null,
              },
            },
            params: {
              feed_character_count: '',
              feed_display_order: '',
              newsfeed_layout: '',
              show_feed_description: '',
              show_feed_image: '',
              show_item_description: '',
            },
          });
        }))
        .then((response) => {
          feedId = response.body.data.id;

          cy.wrap(response)
            .its('body.data.attributes.tags')
            .should('have.property', `${tagId}`, 'automated test feed tag');

          return cy.api_patch(`/newsfeeds/feeds/${feedId}`, {
            name: 'updated automated test feed with tag',
          });
        })
        .then((response) => {
          cy.wrap(response)
            .its('body.data.attributes.tags')
            .should('have.property', `${tagId}`, 'automated test feed tag');

          return cy.api_get(`/newsfeeds/feeds/${feedId}`);
        })
        .then((response) => {
          cy.wrap(response)
            .its('body.data.attributes.tags')
            .should('have.property', `${tagId}`, 'automated test feed tag');
        });
    });

    it('can update a feed', () => {
      cy.db_createNewsFeed({ name: 'automated test contact', access: 1, link: `${Cypress.config('baseUrl')}/tests/System/data/com_newsfeeds/${file}.xml` })
        .then((feed) => cy.api_patch(`/newsfeeds/feeds/${feed.id}`, { name: 'updated automated test feed' }))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'updated automated test feed'));
    });

    it('can delete a feed', () => {
      cy.db_createNewsFeed({
        name: 'automated test contact', access: 1, link: `${Cypress.config('baseUrl')}/tests/System/data/com_newsfeeds/${file}.xml`, published: -2,
      })
        .then((feed) => cy.api_delete(`/newsfeeds/feeds/${feed.id}`));
    });
  });
});
