describe('Test that the Contact Form', () => {
  it('can display a Contact Form', () => {
    cy.db_getUserId().then((id) => cy.db_createContact({ name: 'contact 1', user_id: id }))
      .then((contactId) => {
        cy.visit(`index.php?option=com_contact&view=contact&id='${contactId}'`);

        cy.contains('Contact Form');
        cy.get('.m-0').should('exist');
      });
  });

  it.only('can display a form view of contact', () => {
    cy.doFrontendLogin(Cypress.env('username'), Cypress.env('password'));
    cy.visit('index.php?option=com_contact&view=form&layout=edit');
    cy.get('#jform_name').type('test contact 1');
    cy.get('.mb-2 > .btn-primary').click();
    cy.visit('index.php?option=com_contact&view=category&id=4');

    cy.contains('test contact 1').should('exist');
  });

  it('can display an added field', () => {
    cy.db_createFieldGroup({ title: 'automated test_field group', context: 'com_contact.mail' })
      .then((id) => cy.db_createField({
        group_id: id, context: 'com_contact.mail', type: 'checkboxes', fieldparams: JSON.stringify({ options: { options0: { name: 'test value', value: '' } } }),
      }))
      .then(() => cy.db_getUserId())
      .then((userId) => cy.db_createContact({ name: 'automated test contact 1', user_id: userId }))
      .then((contactId) => {
        cy.visit(`index.php?option=com_contact&view=contact&id='${contactId}'`);

        cy.contains('automated test_field group').should('exist');
        cy.contains('test field').should('exist');
        cy.contains('test value').should('exist');
      });
  });
});
