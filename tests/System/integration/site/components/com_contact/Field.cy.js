describe('Test that the Contact Field', () => {
  it('can display a added field', () => {
    cy.db_createFieldGroup({ title: 'automated test field group', context: 'com_contact.mail' })
      .then((id) => {
        cy.db_createField({
          group_id: id, context: 'com_contact.mail', type: 'checkboxes', fieldparams: '{"options":{"options0":{"name":"Facebook","value":""}}}',
        });
      });
    cy.db_getUserId().then((id) => cy.db_createContact({ name: 'automated test contact 1', user_id: id }))
      .then((contactId) => {
        cy.visit(`index.php?option=com_contact&view=contact&id='${contactId}'`);

        cy.contains('automated test field group');
        cy.get(':nth-child(2) > .control-group > .control-label').contains('test field');
        cy.get('#jform_com_fields_test_field').contains('Facebook');
      });
  });
});
