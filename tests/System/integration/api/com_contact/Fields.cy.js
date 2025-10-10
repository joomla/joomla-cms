describe('Test that field contact API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__fields'));

  ['contact', 'mail', 'categories'].forEach((context) => {
    it(`can deliver a list of fields (${context})`, () => {
      cy.db_createField({ title: `automated test field contacts ${context}`, context: `com_contact.${context}` })
        .then(() => cy.api_get(`/fields/contacts/${context}`))
        .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('title')
          .should('include', `automated test field contacts ${context}`));
    });

    it(`can deliver a single field (${context})`, () => {
      cy.db_createField({ title: `automated test field contacts ${context}`, context: `com_contact.${context}` })
        .then((id) => cy.api_get(`/fields/contacts/${context}/${id}`))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `automated test field contacts ${context}`));
    });

    it(`can create a field (${context})`, () => {
      cy.api_post(`/fields/contacts/${context}`, {
        title: `automated test field contacts ${context}`,
        access: 1,
        context: `com_contact.${context}`,
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
          .should('include', `automated test field contacts ${context}`));
    });

    it(`can update a field (${context})`, () => {
      cy.db_createField({ title: 'automated test field', context: `com_contact.${context}` })
        .then((id) => cy.api_patch(`/fields/contacts/${context}/${id}`, { title: `updated automated test field ${context}` }))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `updated automated test field ${context}`));
    });

    it(`can delete a field (${context})`, () => {
      cy.db_createField({ title: 'automated test field', context: `com_contact.${context}`, state: -2 })
        .then((id) => cy.api_delete(`/fields/contacts/${context}/${id}`));
    });
  });
});
