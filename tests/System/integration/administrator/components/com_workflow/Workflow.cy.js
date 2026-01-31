describe('Test in backend that the workflow form', () => {
  before(() => cy.task('writeRelativeFile', { path: 'administrator/language/overrides/en-GB.override.ini', content: 'AUTOMATED_TEST_WORKFLOW="Test workflow translated"' }));
  beforeEach(() => cy.doAdministratorLogin());
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__workflow_stages WHERE workflow_id IN (SELECT id FROM #__workflows WHERE title = 'AUTOMATED_TEST_WORKFLOW')");
    cy.task('queryDB', "DELETE FROM #__workflows WHERE title = 'AUTOMATED_TEST_WORKFLOW'");
  });
  after(() => cy.task('deleteRelativePath', 'administrator/language/overrides/en-GB.override.ini'));

  it('can create a workflow', () => {
    cy.visit('/administrator/index.php?option=com_workflow&task=workflow.add&extension=com_content.article');
    cy.title().should('contain', 'Add Workflow');
    cy.get('h1.page-title').should('contain', 'Add Workflow');
    cy.get('#jform_title').clear().type('AUTOMATED_TEST_WORKFLOW');
    cy.get('#jform_description').clear().type('automated test workflow');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Item saved.');
    cy.title().should('contain', 'Edit Workflow');
    cy.get('h1.page-title').should('contain', 'Edit Workflow');
    cy.get('#jform_title').should('have.value', 'AUTOMATED_TEST_WORKFLOW');
    cy.get('#workflow_title_translation').should('have.value', 'Test workflow translated');

    cy.clickToolbarButton('Cancel');
    cy.get('h1.page-title').should('contain', 'Workflows');

    cy.get('table#workflowList').contains('Test workflow translated');
    cy.get('table#workflowList').contains('automated test workflow');
  });

  it('can edit a workflow', () => {
    cy.db_createWorkflow({ title: 'Test workflow', extension: 'com_content.article', published: 0 }).then((workflow) => {
      cy.visit(`/administrator/index.php?option=com_workflow&task=workflow.edit&extension=com_content.article&id=${workflow.id}`);
      cy.get('h1.page-title').should('contain', 'Edit Workflow');
      cy.get('#jform_published').find(':selected').should('contain', 'Disabled');
      cy.get('#jform_published').select('Enabled');
      cy.get('#jform_description').clear().type('edited workflow');
      cy.clickToolbarButton('Save');

      cy.checkForSystemMessage('Item saved.');
      cy.get('#jform_published').find(':selected').should('contain', 'Enabled');
      cy.clickToolbarButton('Save & Close');

      cy.checkForSystemMessage('Item saved.');
      cy.get('table#workflowList').contains('edited workflow');
    });
  });

  it('can edit a workflow (list view)', () => {
    cy.db_createWorkflow({ title: 'Test workflow', extension: 'com_content.article' });
    cy.visit('/administrator/index.php?option=com_workflow&view=workflows&extension=com_content.article');
    cy.get('table#workflowList a').contains('Test workflow').click();
    cy.get('h1.page-title').should('contain', 'Edit Workflow');
    cy.get('#jform_title').clear().type('workflow title');
    cy.get('#toolbar-dropdown-save-group .btn.btn-success.dropdown-toggle-split').click();
    cy.clickToolbarButton('Save & New');

    cy.checkForSystemMessage('Item saved.');
    cy.get('h1.page-title').should('contain', 'Add Workflow');
  });

  it('redirects to the correct list view', () => {
    cy.visit('/administrator/index.php?option=com_workflow&task=workflow.add&extension=com_content.article');
    cy.intercept('**/administrator/index.php?option=com_workflow&view=workflows&extension=com_content.article').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
    cy.title().should('contain', 'Workflows');
  });
});
