describe('Test that banners API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__banners'));

  it('can deliver a list of banners', () => {
    cy.db_createBanner({ name: 'automated test banner' })
      .then(() => cy.api_get('/banners'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('name')
        .should('include', 'automated test banner'));
  });

  it('can deliver a list of unpublished banners', () => {
    cy.db_createBanner({ name: 'automated test banner', state: 0 })
      .then(() => cy.api_get('/banners?filter[state]=0'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('name')
        .should('include', 'automated test banner'));
  });

  it('can deliver a list of published banners', () => {
    cy.db_createBanner({ name: 'automated test banner', state: 1 })
      .then(() => cy.api_get('/banners?filter[state]=1'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('name')
        .should('include', 'automated test banner'));
  });

  it('can deliver a single banner', () => {
    cy.db_createBanner({ name: 'automated test banner' })
      .then((banner) => cy.api_get(`/banners/${banner.id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'automated test banner'));
  });

  it('can create a banner', () => {
    cy.db_createCategory({ extension: 'com_banners' })
      .then((categoryId) => cy.api_post('/banners', {
        name: 'automated test banner',
        alias: 'test-banner',
        catid: categoryId,
        state: 1,
        language: '*',
        description: '',
        custombannercode: '',
        params: {
          imageurl: '', width: '', height: '', alt: '',
        },
      }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'automated test banner'));
  });

  it('can create a banner with multiple secondary categories', () => {
    let bannerId = 0;
    let secondaryCategoryId1 = 0;
    let secondaryCategoryId2 = 0;

    cy.db_createCategory({
      title: 'api primary category',
      alias: 'api-primary-category-multi-banner',
      path: 'api-primary-category-multi-banner',
      extension: 'com_banners',
    })
      .then((primaryCategoryId) => cy.db_createCategory({
        title: 'api secondary category 1',
        alias: 'api-secondary-category-banner-1',
        path: 'api-secondary-category-banner-1',
        extension: 'com_banners',
      }).then((categoryId) => {
        secondaryCategoryId1 = categoryId;

        return cy.db_createCategory({
          title: 'api secondary category 2',
          alias: 'api-secondary-category-banner-2',
          path: 'api-secondary-category-banner-2',
          extension: 'com_banners',
        });
      }).then((categoryId) => {
        secondaryCategoryId2 = categoryId;

        return cy.api_post('/banners', {
          name: 'automated multiple secondary categories banner',
          alias: 'automated-multiple-secondary-categories-banner',
          catid: primaryCategoryId,
          secondary_categories: [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ],
          state: 1,
          language: '*',
          description: '',
          custombannercode: '',
          params: {
            imageurl: '', width: '', height: '', alt: '',
          },
        });
      }))
      .then((response) => {
        bannerId = response.body.data.id;

        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ]);

        return cy.api_get(`/banners/${bannerId}`);
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

  it('can replace secondary categories for a banner', () => {
    let bannerId = 0;
    let secondaryCategoryId1 = 0;
    let secondaryCategoryId2 = 0;

    cy.db_createCategory({
      title: 'api primary category',
      alias: 'api-primary-category-replace-banner',
      path: 'api-primary-category-replace-banner',
      extension: 'com_banners',
    })
      .then((primaryCategoryId) => cy.db_createCategory({
        title: 'api secondary category 1',
        alias: 'api-secondary-category-replace-banner-1',
        path: 'api-secondary-category-replace-banner-1',
        extension: 'com_banners',
      }).then((categoryId) => {
        secondaryCategoryId1 = categoryId;

        return cy.db_createCategory({
          title: 'api secondary category 2',
          alias: 'api-secondary-category-replace-banner-2',
          path: 'api-secondary-category-replace-banner-2',
          extension: 'com_banners',
        });
      }).then((categoryId) => {
        secondaryCategoryId2 = categoryId;

        return cy.api_post('/banners', {
          name: 'replace secondary categories banner',
          alias: 'replace-secondary-categories-banner',
          catid: primaryCategoryId,
          secondary_categories: [secondaryCategoryId1],
          state: 1,
          language: '*',
          description: '',
          custombannercode: '',
          params: {
            imageurl: '', width: '', height: '', alt: '',
          },
        });
      }))
      .then((response) => {
        bannerId = response.body.data.id;

        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [secondaryCategoryId1]);

        return cy.api_patch(`/banners/${bannerId}`, {
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

        return cy.api_get(`/banners/${bannerId}`);
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ]);

        return cy.api_patch(`/banners/${bannerId}`, {
          name: 'updated banner name',
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ]);

        return cy.api_patch(`/banners/${bannerId}`, {
          secondary_categories: [secondaryCategoryId2],
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [secondaryCategoryId2]);

        return cy.api_patch(`/banners/${bannerId}`, {
          secondary_categories: [],
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', []);

        return cy.api_patch(`/banners/${bannerId}`, {
          name: 'updated again',
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', []);
      });
  });

  it('can update a banner', () => {
    cy.db_createBanner({ name: 'automated test banner' })
      .then((banner) => cy.api_patch(`/banners/${banner.id}`, { name: 'updated automated test banner' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'updated automated test banner'));
  });

  it('can delete a banner', () => {
    cy.db_createBanner({ name: 'automated test banner', state: -2 })
      .then((banner) => cy.api_delete(`/banners/${banner.id}`));
  });

  it('check correct response for delete a not existent banner', () => {
    cy.api_getBearerToken().then((token) => {
      cy.request({
        method: 'DELETE',
        url: '/api/index.php/v1/banners/9999',
        headers: {
          Authorization: `Bearer ${token}`,
        },
        failOnStatusCode: false,
      }).then((response) => {
        expect(response.status).to.equal(404);
        expect(response.body.data.message).to.include('Resource not found');
      });
    });
  });

  it('cannot delete a banner that is not trashed', () => {
    cy.db_createBanner({ name: 'automated test banner' })
      .then((banner) => {
        cy.api_getBearerToken().then((token) => {
          cy.request({
            method: 'DELETE',
            url: `/api/index.php/v1/banners/${banner.id}`,
            headers: {
              Authorization: `Bearer ${token}`,
            },
            failOnStatusCode: false,
          }).then((response) => {
            expect(response.status).to.equal(409);
            expect(response.body.data.message).to.include('must be trashed before it can be deleted');
          });
        });
      });
  });
});
