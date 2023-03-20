describe('Test that the front page', () => {
  it('can display featured contact', () => {
    cy.db_createContact({ name: 'automated test contact 1' })
      .then(() => cy.db_createContact({ name: 'automated test contact 2' }))
      .then(() => cy.db_createContact({ name: 'automated test contact 3' }))
      .then(() => cy.db_createContact({ name: 'automated test contact 4' }))
      .then(() => cy.db_createMenuItem({ title: 'automated test Contact', link: 'index.php?option=com_contact&view=featured', path: '?option=com_contact&view=featured' }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test Contact)').click();

        cy.contains('automated test contact 1');
        cy.contains('automated test contact 2');
        cy.contains('automated test contact 3');
        cy.contains('automated test contact 4');
      });
  });
});
