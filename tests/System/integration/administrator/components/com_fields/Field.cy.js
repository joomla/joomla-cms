describe('Test that the field back end form', () => {
  beforeEach(() => cy.doAdministratorLogin());
  afterEach(() => cy.task('queryDB', "DELETE FROM #__fields WHERE title = 'Test field'"));

  it('can create a field', () => {
    cy.visit('administrator/index.php?option=com_fields&task=field.add&context=com_content.article');
    cy.get('#jform_title').clear().type('Test field');
    cy.clickToolbarButton('Save & Close');

    cy.get('#system-message-container').contains('Field saved').should('exist');
    cy.contains('Test field');
  });

  it('can edit a field', () => {
    cy.db_createField({ title: 'Test field' }).then((id) => {
      cy.visit(`administrator/index.php?option=com_fields&task=field.edit&id=${id}&context=com_content.article`);
      cy.get('#jform_title').clear().type('Test field edited');
      cy.clickToolbarButton('Save & Close');

      cy.contains('Test field edited');
    });
  });
});
