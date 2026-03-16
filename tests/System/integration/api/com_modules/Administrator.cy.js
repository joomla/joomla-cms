describe('Test that modules administrator API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__modules WHERE title = 'automated test administrator module'"));

  it('can deliver a list of administrator modules', () => {
    cy.api_get('/modules/administrator')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('module')
        .should('include', 'mod_sampledata'));
  });

  it('can deliver a single administrator module', () => {
    cy.db_createModule({ title: 'automated test administrator module', client_id: 1 })
      .then((module) => cy.api_get(`/modules/administrator/${module}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test administrator module'));
  });

  it('can create an administrator module', () => {
    cy.api_post('/modules/administrator', {
      access: '1',
      assigned: [
        '101',
        '105',
      ],
      assignment: '0',
      client_id: '1',
      language: '0',
      module: 'mod_version',
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
      title: 'automated test administrator module',
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test administrator module'));
  });

  it('can update an administrator module', () => {
    cy.db_createModule({ title: 'automated test administrator module', client_id: 1 })
      .then((id) => {
        const updatedModuleData = {
          published: -2,
        };
        return cy.api_patch(`/modules/administrator/${id}`, updatedModuleData);
      })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('published')
        .should('equal', -2));
  });

  it('can delete a administrator module', () => {
    cy.db_createModule({ title: 'automated test administrator module', published: -2, client_id: 1 })
      .then((module) => cy.api_delete(`/modules/administrator/${module}`))
      .then((response) => cy.wrap(response).its('status').should('equal', 204));
  });
});
