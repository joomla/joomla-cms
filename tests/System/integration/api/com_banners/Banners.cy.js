describe('Test that banners API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__banners'));

  it('can deliver a list of banners', () => {
    cy.db_createBanner({ name: 'automated test banner' })
      .then(() => cy.api_get('/banners'))
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
        url: `/api/index.php/v1/banners/9999`,
        headers: {
          Authorization: `Bearer ${token}`,
        },
        failOnStatusCode: false
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
            failOnStatusCode: false
          }).then((response) => {
            expect(response.status).to.equal(409);
            expect(response.body.data.message).to.include('must be trashed before it can be deleted');
          });
        });
      });
  });
});
