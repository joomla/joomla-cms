describe('Test that the Contact Field', () => {
  it('can display an added field', () => {
    cy.db_createFieldGroup({ title: 'automated test field group', context: 'com_contact.mail' })
      .then((id) => cy.db_createField({
        group_id: id, context: 'com_contact.mail', type: 'checkboxes', fieldparams: JSON.stringify({ options: { options0: { name: 'test value', value: '' } } }),
      })
        .then(() => cy.db_getUserId().then((userId) => cy.db_createContact({ name: 'automated test contact 1', user_id: userId }))))
      .then((contactId) => cy.visit(`index.php?option=com_contact&view=contact&id='${contactId}'`));

    cy.contains('automated test field group');
    cy.get(':nth-child(2) > .control-group > .control-label').contains('test field');
    cy.get('#jform_com_fields_test_field').contains('test value');
  });
});
