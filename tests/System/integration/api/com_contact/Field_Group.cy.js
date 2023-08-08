describe('Test that group field contact API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__fields_groups'));

  it('can deliver a list of group fields', () => {
    cy.db_createFieldGroup({ title: 'automated test group field', context: 'com_contact.contact' })
      .then(() => cy.api_get('/fields/groups/contacts/contact'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test group field'));
  });

  it.only('can create a group field', () => {
    cy.api_post('/fields/groups/contacts/contact', {
      title: 'automated test group field',
      access: 1,
      context: 'com_contact.contact',
      default_value: '',
      description: '',
      group_id: 0,
      label: 'contact group field',
      language: '*',
      name: 'contact-group_field',
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
        .should('include', 'automated test group field'));
  });

  it('can update a group field', () => {
    cy.db_createFieldGroup({ title: 'automated test group field', access: 1, context: 'com_contact.contact' })
      .then((id) => cy.api_patch(`/fields/groups/contacts/contact/${id}`, { title: 'updated automated test group field', context: 'com_contact.contact' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test group field'));
  });

  it('can delete a group field', () => {
    cy.db_createFieldGroup({ title: 'automated test group field', state: -2 })
      .then((id) => cy.api_delete(`/fields/groups/contacts/contact/${id}`));
  });
});
