describe('Test that modules site API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__modules WHERE title = 'automated test site module'"));

  it('can deliver a list of site modules', () => {
    cy.api_get('/modules/site')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('module')
        .should('include', 'mod_breadcrumbs'));
  });

  it('can deliver a single site module', () => {
    cy.db_createModule({ title: 'automated test site module' })
      .then((module) => cy.api_get(`/modules/site/${module}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test site module'));
  });

  it('can create a site module', () => {
    cy.api_post('/modules/site', {
      access: '1',
      assigned: [
        '101',
        '105',
      ],
      assignment: '0',
      client_id: '0',
      language: '0',
      module: 'mod_articles_archive',
      note: '',
      ordering: '1',
      params: {
        bootstrap_size: '0',
        cache: '1',
        cache_time: '900',
        cachemode: 'static',
        count: '10',
        header_class: '',
        header_tag: 'h3',
        layout: '_:default',
        module_tag: 'div',
        moduleclass_sfx: '',
        style: '0',
      },
      position: '',
      publish_down: '',
      publish_up: '',
      published: '1',
      showtitle: '1',
      title: 'automated test site module',
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test site module'));
  });

  it('can update a site module', () => {
    cy.db_createModule({ title: 'automated test site module' })
      .then((id) => {
        const updatedModuleData = {
          published: -2,
        };
        return cy.api_patch(`/modules/site/${id}`, updatedModuleData);
      })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('published')
        .should('equal', -2));
  });

  it('can delete a site module', () => {
    cy.db_createModule({ title: 'automated test site module', published: -2 })
      .then((module) => cy.api_delete(`/modules/site/${module}`))
      .then((response) => cy.wrap(response).its('status').should('equal', 204));
  });
});
