describe('Test that newsfeed API endpoint', () => {
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
