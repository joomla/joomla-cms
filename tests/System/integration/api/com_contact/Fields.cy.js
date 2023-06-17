describe('Test that field contact API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__fields'));

  it('can deliver a list of fields', () => {
    cy.db_createField({ title: 'automated test field', context: 'com_contact.contact' })
      .then(() => cy.api_get('/fields/contacts/contact'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test field'));
  });

  it('can create a field', () => {
    cy.api_post('/fields/contacts/contact', {
      title: 'automated test field',
      access: 1,
      context: 'com_contact.contact',
      default_value: '',
      description: '',
      group_id: 0,
      label: 'contact field',
      language: '*',
      name: 'contact-field',
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
        .should('include', 'automated test field'));
  });

  it('can update a field', () => {
    cy.db_createField({ title: 'automated test field', context: 'com_contact.contact' })
      .then((id) => cy.api_patch(`/fields/contacts/contact/${id}`, { title: 'updated automated test field' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test field'));
  });

  it('can delete a field', () => {
    cy.db_createField({ title: 'automated test field', state: -2 })
      .then((id) => cy.api_delete(`/fields/contacts/contact/${id}`));
  });
});
