describe('Test in frontend that the contact featured view', () => {
  it('can display featured contacts', () => {
    cy.db_createContact({ name: 'automated test contact 1', featured: 1 })
      .then(() => cy.db_createContact({ name: 'automated test contact 2', featured: 1 }))
      .then(() => cy.db_createContact({ name: 'automated test contact 3', featured: 1 }))
      .then(() => cy.db_createContact({ name: 'automated test contact 4', featured: 1 }))
      .then(() => cy.db_createMenuItem({ title: 'automated test', link: 'index.php?option=com_contact&view=featured' }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test)').click();

        cy.contains('automated test contact 1');
        cy.contains('automated test contact 2');
        cy.contains('automated test contact 3');
        cy.contains('automated test contact 4');
      });
  });

  it('can not display not featured contacts', () => {
    cy.db_createContact({ name: 'automated test contact 1', featured: 0 })
      .then(() => cy.db_createContact({ name: 'automated test contact 2', featured: 0 }))
      .then(() => cy.db_createMenuItem({ title: 'automated test', link: 'index.php?option=com_contact&view=featured' }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test)').click();

        cy.contains('automated test contact 1').should('not.exist');
        cy.contains('automated test contact 2').should('not.exist');
      });
  });
});
