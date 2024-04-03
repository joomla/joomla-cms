describe('Test that group field content API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__fields_groups'));

  ['article', 'categories'].forEach((context) => {
    const endpoint = context === 'article' ? 'articles' : context;
    it(`can deliver a list of group fields (${context})`, () => {
      cy.db_createFieldGroup({ title: `automated test group field content ${context}`, context: `com_content.${context}` })
        .then(() => cy.api_get(`/fields/groups/content/${endpoint}`))
        .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('title')
          .should('include', `automated test group field content ${context}`));
    });

    it(`can deliver a single group field (${context})`, () => {
      cy.db_createFieldGroup({ title: `automated test group field content ${context}`, context: `com_content.${context}` })
        .then((id) => cy.api_get(`/fields/groups/content/${endpoint}/${id}`))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `automated test group field content ${context}`));
    });

    it(`can create a group field (${context})`, () => {
      cy.api_post(`/fields/groups/content/${endpoint}`, {
        title: `automated test group field content ${context}`,
        access: 1,
        context: `com_content.${context}`,
        default_value: '',
        description: '',
        group_id: 0,
        label: 'content group field',
        language: '*',
        name: 'content-group_field',
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
          .should('include', `automated test group field content ${context}`));
    });

    it(`can update a group field (${context})`, () => {
      cy.db_createFieldGroup({ title: 'automated test group field', access: 1, context: `com_content.${context}` })
        .then((id) => cy.api_patch(`/fields/groups/content/${endpoint}/${id}`, { title: `updated automated test group field ${context}` }))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `updated automated test group field ${context}`));
    });

    it(`can delete a group field (${context})`, () => {
      cy.db_createFieldGroup({ title: 'automated test group field', context: `com_content.${context}`, state: -2 })
        .then((id) => cy.api_delete(`/fields/groups/content/${endpoint}/${id}`));
    });
  });
});
