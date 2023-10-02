describe('Test that group field user API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__fields_groups'));

  it('can deliver a list of group fields', () => {
    cy.db_createFieldGroup({ title: 'automated test group field users', context: 'com_users.user' })
      .then(() => cy.api_get('/fields/groups/users'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test group field users'));
  });

  it('can deliver a single group field', () => {
    cy.db_createFieldGroup({ title: 'automated test group field users', context: 'com_users.user' })
      .then((id) => cy.api_get(`/fields/groups/users/${id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test group field users'));
  });

  it('can create a group field', () => {
    cy.api_post('/fields/groups/users', {
      title: 'automated test group field users',
      access: 1,
      context: 'com_users.user',
      default_value: '',
      description: '',
      group_id: 0,
      label: 'user group field',
      language: '*',
      name: 'user-group_field',
      note: '',
      params: {
        class: '',
        display: '2',
        display_readonly: '2',
        hint: '',
        label_class: '',
        label_render_class: '',
        layout: '',
        prefix: '',
        render_class: '',
        show_on: '',
        showlabel: '1',
        suffix: '',
      },
      required: 0,
      state: 1,
      type: 'text',
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test group field users'));
  });

  it('can update a group field ()', () => {
    cy.db_createFieldGroup({ title: 'automated test group field', access: 1, context: 'com_users.user' })
      .then((id) => cy.api_patch(`/fields/groups/users/${id}`, { title: 'updated automated test group field' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test group field'));
  });

  it('can delete a group field', () => {
    cy.db_createFieldGroup({ title: 'automated test group field', context: 'com_users.user', state: -2 })
      .then((id) => cy.api_delete(`/fields/groups/users/${id}`));
  });
});
