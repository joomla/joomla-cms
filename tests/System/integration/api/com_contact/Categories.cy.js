describe('Test that content categories API endpoint', () => {
  it('can display a list of categories', () => {
    cy.db_createCategory({ title: 'automated test category', extension: 'com_contact' })
      .then((id) => cy.db_createContact({ name: 'automated test contact', catid: id }))
      .then(() => cy.api_get('/contacts/categories'))
      .then((response) => cy.wrap(response.body.data.map((category) => ({ title: category.attributes.title })))
        .should('deep.include', { title: 'automated test category' }));
  });
});
