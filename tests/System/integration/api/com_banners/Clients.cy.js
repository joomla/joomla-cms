describe('Test that banners clients API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__banner_clients'));

  it('can deliver a list of clients', () => {
    cy.db_createBannerClient({ name: 'automated test banner client' })
      .then(() => cy.api_get('/banners/clients'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('name')
        .should('include', 'automated test banner client'));
  });

  it('can deliver a single client', () => {
    cy.db_createBannerClient({ name: 'automated test banner client' })
      .then((bannerclient) => cy.api_get(`/banners/clients/${bannerclient.id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'automated test banner client'));
  });

  it('can create a client', () => {
    cy.api_post('/banners/clients', {
      name: 'automated test banner client',
      contact: 'automated test banner client',
      state: 1,
      extrainfo: '',
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'automated test banner client'));
  });

  it('can update a client', () => {
    cy.db_createBannerClient({ name: 'automated test banner client' })
      .then((bannerclient) => cy.api_patch(`/banners/clients/${bannerclient.id}`, { name: 'updated automated test banner client' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'updated automated test banner client'));
  });

  it('can delete a client', () => {
    cy.db_createBannerClient({ name: 'automated test banner client', state: -2 })
      .then((bannerclient) => cy.api_delete(`/banners/clients/${bannerclient.id}`));
  });
});
