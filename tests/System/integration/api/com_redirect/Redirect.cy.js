describe('Test that redirect API endpoint', () => {
  it('can create a redirect', () => {
    cy.api_post('/redirects', {
      comment: 'automated test redirect',
      header: 301,
      hits: 1,
      new_url: '/content/art/99',
      old_url: '/content/art/12',
      published: 1,
      referer: '',
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('comment')
        .should('include', 'automated test redirect'));
  });

  it('can deliver a list of redirects', () => {
    cy.api_get('/redirects')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('comment')
        .should('equal', 'automated test redirect'));
  });

  it('can deliver a single redirect', () => {
    cy.api_get('/redirects')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id'))
      .then((id) => {
        cy.api_get(`/redirects/${id}`)
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('new_url')
            .should('include', '/content/art/99'));
      });
  });

  it('can modify a single redirect', () => {
    cy.api_get('/redirects')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id'))
      .then((id) => {
        const updateRedirect = {
          published: 0,
        };
        cy.api_patch(`/redirects/${id}`, updateRedirect)
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('published')
            .should('equal', 0));
      });
  });

  it('can delete a single redirect', () => {
    cy.api_get('/redirects')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id'))
      .then((id) => {
        const updateRedirect = {
          published: -2,
        };
        cy.api_patch(`/redirects/${id}`, updateRedirect)
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('id')
            .should('equal', Math.abs(`${id}`)));
      })
      .then((id) => {
        cy.api_delete(`/redirects/${id}`)
          .then((response) => cy.wrap(response).its('status').should('equal', 204));
      });
  });
});
