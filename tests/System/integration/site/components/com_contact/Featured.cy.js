describe('Test that the front page', () => {
  it('can display featured contact', () => {
    cy.db_createContact({ name: 'automated test contact 1', featured: 1 })
      .then(() => cy.db_createContact({ name: 'automated test contact 2', featured: 1 }))
      .then(() => cy.db_createContact({ name: 'automated test contact 3', featured: 1 }))
      .then(() => cy.db_createContact({ name: 'automated test contact 4', featured: 1 }))
      .then(() => cy.db_createMenuItem({ title: 'automated test', link: 'index.php?option=com_contact&view=featured', path: '?option=com_contact&view=featured' }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test)').click();

        cy.contains('automated test contact 1');
        cy.contains('automated test contact 2');
        cy.contains('automated test contact 3');
        cy.contains('automated test contact 4');
      });
  });
});
