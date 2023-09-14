describe('Test in frontend that the contact category view', () => {
  it('can display a list of contacts in a menu item', () => {
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

  it('can display a list of contacts without a menu item', () => {
    cy.db_createContact({ name: 'automated test contact 1' })
      .then((contact) => cy.db_createContact({ name: 'automated test contact 2', featured: 1, catid: contact.catid }))
      .then((contact) => cy.db_createContact({ name: 'automated test contact 3', featured: 1, catid: contact.catid }))
      .then((contact) => cy.db_createContact({ name: 'automated test contact 4', featured: 1, catid: contact.catid }))
      .then((contact) => {
        cy.visit(`/index.php?option=com_contact&view=category&id=${contact.catid}`);

        cy.contains('automated test contact 1');
        cy.contains('automated test contact 2');
        cy.contains('automated test contact 3');
        cy.contains('automated test contact 4');
      });
  });

  it('can open the contact form in the default layout', () => {
    cy.db_createContact({ name: 'contact 1' })
      .then((contact) => cy.db_createMenuItem({ title: 'automated test', link: `index.php?option=com_contact&view=category&id=${contact.catid}` }))
      .then(() => {
        cy.doFrontendLogin();
        cy.visit('/');
        cy.get('a:contains(automated test)').click();
        cy.get('a:contains(New Contact)').click();

        cy.get('#adminForm').should('exist');
      });
  });
});
