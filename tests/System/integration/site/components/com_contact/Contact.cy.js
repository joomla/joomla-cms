describe('Test in frontend that the contact details view', () => {
  it('can display a form', () => {
    cy.db_getUserId().then((id) => cy.db_createContact({ name: 'contact 1', user_id: id }))
      .then((contact) => {
        cy.visit(`/index.php?option=com_contact&view=contact&id='${contact.id}'`);

        cy.contains('Contact Form');
        cy.get('.m-0').should('exist');
      });
  });

  it('can display a custom field', () => {
    cy.db_createFieldGroup({ title: 'automated test_field group', context: 'com_contact.mail' })
      .then((id) => cy.db_createField({
        group_id: id, context: 'com_contact.mail', type: 'checkboxes', fieldparams: JSON.stringify({ options: { options0: { name: 'test value', value: '' } } }),
      }))
      .then(() => cy.db_getUserId())
      .then((userId) => cy.db_createContact({ name: 'automated test contact 1', user_id: userId }))
      .then((contact) => {
        cy.visit(`/index.php?option=com_contact&view=contact&id='${contact.id}'`);

        cy.contains('automated test_field group').should('exist');
        cy.contains('test field').should('exist');
        cy.contains('test value').should('exist');
      });
  });
});
