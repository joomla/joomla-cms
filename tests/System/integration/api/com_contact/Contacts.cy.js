describe('Test that contacts API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__contact_details'));

  it('can deliver a list of contacts', () => {
    cy.db_createContact({ name: 'automated test contact' })
      .then(() => cy.api_get('/contacts'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('name')
        .should('include', 'automated test contact'));
  });

  it('can create a contact', () => {
    cy.db_createCategory({ extension: 'com_contacts' })
      .then((categoryId) => cy.api_post('/contacts', {
        name: 'automated test contact',
        alias: 'test-contact',
        catid: categoryId,
        published: 1,
        language: '*',
      }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'automated test contact'));
  });

  it('can update a contact', () => {
    cy.db_createContact({ name: 'automated test contact', access: 1 })
      .then((contact) => cy.api_patch(`/contacts/${contact.id}`, { name: 'updated automated test contact' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'updated automated test contact'));
  });

  it('can delete a contact', () => {
    cy.db_createContact({ name: 'automated test contact', published: -2 })
      .then((contact) => cy.api_delete(`/contacts/${contact.id}`));
  });
});
