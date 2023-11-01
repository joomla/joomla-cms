describe('Test that field content API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__fields'));

  ['article', 'categories'].forEach((context) => {
    const endpoint = context === 'article' ? 'articles' : context;
    it(`can deliver a list of fields (${context})`, () => {
      cy.db_createField({ title: `automated test field content ${context}`, context: `com_content.${context}` })
        .then(() => cy.api_get(`/fields/content/${endpoint}`))
        .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
          .its('title')
          .should('include', `automated test field content ${context}`));
    });

    it(`can deliver a single field (${context})`, () => {
      cy.db_createField({ title: `automated test field content ${context}`, context: `com_content.${context}` })
        .then((id) => cy.api_get(`/fields/content/${endpoint}/${id}`))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `automated test field content ${context}`));
    });

    it(`can create a field (${context})`, () => {
      cy.api_post(`/fields/content/${endpoint}`, {
        title: `automated test field content ${context}`,
        access: 1,
        context: `com_content.${context}`,
        default_value: '',
        description: '',
        group_id: 0,
        label: 'content field',
        language: '*',
        name: `content-field-${context}`,
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
          .should('include', `automated test field content ${context}`));
    });

    it(`can update a field (${context})`, () => {
      cy.db_createField({ title: 'automated test field', context: `com_content.${context}`, access: 1 })
        .then((id) => cy.api_patch(`/fields/content/${endpoint}/${id}`, { title: `updated automated test field ${context}` }))
        .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `updated automated test field ${context}`));
    });

    it(`can delete a field (${context})`, () => {
      cy.db_createField({ title: 'automated test field', context: `com_content.${context}`, state: -2 })
        .then((id) => cy.api_delete(`/fields/content/${endpoint}/${id}`));
    });
  });
});
