describe('Test in backend that the field form', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    // Clear the filter
    cy.visit('/administrator/index.php?option=com_fields&filter=');
  });
  afterEach(() => cy.task('queryDB', "DELETE FROM #__fields WHERE title = 'Test field'"));

  it('can create a field', () => {
    cy.visit('/administrator/index.php?option=com_fields&task=field.add&context=com_content.article');
    cy.get('#jform_title').clear().type('Test field');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Field saved');
    cy.contains('Test field');
  });

  it('check redirection to list view', () => {
    cy.visit('/administrator/index.php?option=com_fields&task=field.add&context=com_content.article');
    cy.intercept('index.php?option=com_fields&view=fields&context=com_content.article').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
  });

  it('can edit a field', () => {
    cy.db_createField({ title: 'Test field' }).then((id) => {
      cy.visit(`/administrator/index.php?option=com_fields&task=field.edit&id=${id}&context=com_content.article`);
      cy.get('#jform_title').clear().type('Test field edited');
      cy.clickToolbarButton('Save & Close');

      cy.contains('Test field edited');
    });
  });

  it('saves the administrator list filter opt-in for a selection field', () => {
    cy.db_createField({
      title: 'Test field',
      type: 'list',
      fieldparams: JSON.stringify({ options: { options0: { name: 'Choice', value: 'choice' } } }),
    }).then((id) => {
      const url = `/administrator/index.php?option=com_fields&task=field.edit&id=${id}&context=com_content.article`;
      const enabled = 'input[name="jform[params][show_in_admin_list_filter]"][value="1"]';

      cy.visit(url);
      cy.get(enabled).check({ force: true });
      cy.clickToolbarButton('Save & Close');
      cy.checkForSystemMessage('Field saved');

      cy.visit(url);
      cy.get(enabled).should('be.checked');
      cy.clickToolbarButton('Cancel');
      cy.db_createArticle({ title: 'Field filter opt-in fixture' });
      cy.visit('/administrator/index.php?option=com_content&view=articles&filter=');
      cy.get(`#filter_customfield_${id}`).should('exist');
    });
  });
});
