describe('Test that the Categories of com_contact ', () => {
  it('can display a list of categories of contacts in menu item', () => {
    cy.db_createCategory({ title: 'automated test category 1', extension: 'com_contact' })
      .then((id) => cy.db_createContact({ name: 'automated test contact 1', catid: id }))
      .then(() => cy.db_createCategory({ title: 'automated test category 2', extension: 'com_contact' }))
      .then(async (id) => {
        await cy.db_createContact({ name: 'automated test contact 2', catid: id });
        await cy.db_createContact({ name: 'automated test contact 3', catid: id });
      })
      .then(() => {
        cy.visit('index.php?option=com_contact&view=categories');

        cy.contains('automated test category 1');
        cy.contains('automated test category 2');
        cy.get(':nth-child(2) > .page-header > .badge').contains('Contact Count: 1');
        cy.get(':nth-child(3) > .page-header > .badge').contains('Contact Count: 2');
      });
  });
});
