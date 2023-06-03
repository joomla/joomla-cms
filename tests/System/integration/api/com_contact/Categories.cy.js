describe('Test that contact categories API endpoint', () => {
  it('can display a list of categories', () => {
    cy.db_createCategory({ title: 'automated test category', extension: 'com_contact' })
      .then((id) => cy.db_createContact({ name: 'automated test contact', catid: id }))
      .then(() => cy.api_get('/contacts/categories'))
      .then((response) => cy.wrap(response).its('body').its('data[0]').its('attributes')
        .its('title')
        .should('include', 'automated test category'));
  });
});
