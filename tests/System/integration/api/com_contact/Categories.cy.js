describe('Test that content categories API endpoint', () => {
  it('can display a list of categories', () => {
    cy.db_createCategory({ title: 'automated test category', extension: 'com_contact' })
      .then((id) => cy.db_createContact({ name: 'automated test contact', catid: id }))
      .then(() => cy.api_get('/contacts/categories'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test category'));
  });
});
