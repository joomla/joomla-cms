describe('Test that group field contact API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__fields_groups'));

  ['contact', 'mail', 'categories'].forEach((context) => {
    it(`can deliver a list of group fields (${context})`, () => {
      cy.db_createFieldGroup({ title: `automated test group field contacts ${context}`, context: `com_contact.${context}` })
        .then(() => cy.api_get(`/fields/groups/contacts/${context}`))
        .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('title')
          .should('include', `automated test group field contacts ${context}`));
    });

    it(`can deliver a single group field (${context})`, () => {
      cy.db_createFieldGroup({ title: `automated test group field contacts ${context}`, context: `com_contact.${context}` })
        .then((id) => cy.api_get(`/fields/groups/contacts/${context}/${id}`))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `automated test group field contacts ${context}`));
    });

    it(`can create a group field (${context})`, () => {
      cy.api_post(`/fields/groups/contacts/${context}`, {
        title: `automated test group field contacts ${context}`,
        access: 1,
        context: `com_contact.${context}`,
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
          .should('include', `automated test group field contacts ${context}`));
    });

    it(`can update a group field (${context})`, () => {
      cy.db_createFieldGroup({ title: 'automated test group field', access: 1, context: `com_contact.${context}` })
        .then((id) => cy.api_patch(`/fields/groups/contacts/${context}/${id}`, { title: `updated automated test group field ${context}` }))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `updated automated test group field ${context}`));
    });

    it(`can delete a group field (${context})`, () => {
      cy.db_createFieldGroup({ title: 'automated test group field', context: `com_contact.${context}`, state: -2 })
        .then((id) => cy.api_delete(`/fields/groups/contacts/${context}/${id}`));
    });
  });
});
