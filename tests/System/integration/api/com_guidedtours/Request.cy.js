describe('Test that guided tours API endpoint', () => {
  it('can deliver a list of tours', () => {
    cy.db_createTour({ title: 'automated test tour' })
      .then(() => cy.api_get('/tours'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test tour'));
  });

  it('can deliver a single tour', () => {
    cy.db_createTour({ title: 'automated testing tour' })
      .then((tour) => cy.api_get(`/tours/${tour.id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated testing tour'));
  });

  it('can create a tour', () => {
    cy.api_post('/tours', {
      title: 'automated test tour',
      uid: 'testing-tour',
      description: 'Testing series tour data',
      extensions: '["*"]',
      url: 'administrator/index.php?option=com_content&view=articles',
      published: 1,
      language: '*',
      access: 1,
      // Enabling this affects other tests currently as we don't clean up these tours!
      autostart: 0,
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test tour'));
  });

  it('can update a tour', () => {
    cy.db_createTour({ title: 'automated test tour' })
      .then((tour) => cy.api_patch(`/tours/${tour.id}`, { title: 'updated automated test tour' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test tour'));
  });

  it('can delete a tour', () => {
    cy.db_createTour({ title: 'automated test tour', published: -2 })
      .then((article) => cy.api_delete(`/tours/${article.id}`));
  });

  it('can deliver a list of steps for a tour', () => {
    cy.db_createTour({ title: 'automated test tour' })
      .then((tourCreateResponse) => {
        cy.db_createTourStep(tourCreateResponse.id, { title: 'automated test tour step' })
          .then(() => cy.api_get(`/tours/${tourCreateResponse.id}/steps`))
          .then((response) => {
            cy.wrap(response).its('body').its('data.0').its('attributes')
              .its('title')
              .should('include', 'automated test tour step');
          });
      });
  });

  it('can deliver a single step for a tour', () => {
    cy.db_createTour({ title: 'automated test tour' })
      .then((tourCreateResponse) => {
        cy.db_createTourStep(tourCreateResponse.id, { title: 'automated test tour step' })
          .then((step) => cy.api_get(`/tours/${tourCreateResponse.id}/steps/${step.id}`))
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('title')
            .should('include', 'automated test tour step'));
      });
  });

  it('can create a step', () => {
    cy.db_createTour({ title: 'automated test tour' })
      .then((tourCreateResponse) => {
        cy.api_post(`/tours/${tourCreateResponse.id}/steps`, {
          title: 'automated test tour step',
          published: 1,
          description: 'Test tour data - test step',
          position: 'top',
          target: '#testElement',
          type: 2,
          interactive_type: 1,
          url: 'administrator/index.php?option=com_content&view=articles',
          language: '*',
          created: '2024-11-04 22:00:00',
          modified: '2024-11-04 22:00:00',
        })
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('title')
            .should('include', 'automated test tour step'));
      });
  });

  it('can update a step', () => {
    cy.db_createTour({ title: 'automated test tour' })
      .then((tourCreateResponse) => {
        cy.db_createTourStep(tourCreateResponse.id, { title: 'automated test tour step' })
          .then((step) => cy.api_patch(`/tours/${tourCreateResponse.id}/steps/${step.id}`, { title: 'updated automated test tour step' }))
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('title')
            .should('include', 'updated automated test tour step'));
      });
  });

  it('can delete a step for a tour', () => {
    cy.db_createTour({ title: 'automated test tour' })
      .then((tourCreateResponse) => {
        cy.db_createTourStep(tourCreateResponse.id, { title: 'automated test tour step', published: -2 })
          .then((step) => cy.api_delete(`/tours/${tourCreateResponse.id}/steps/${step.id}`));
      });
  });
});
