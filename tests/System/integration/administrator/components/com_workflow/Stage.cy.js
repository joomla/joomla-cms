describe('Test in backend that the stage form', () => {
  before(() => cy.task('writeRelativeFile', { path: 'administrator/language/overrides/en-GB.override.ini', content: 'AUTOMATED_TEST_STAGE="Test stage translated"' }));
  beforeEach(() => cy.doAdministratorLogin());
  afterEach(() => cy.task('queryDB', "DELETE FROM #__workflow_stages WHERE title = 'AUTOMATED_TEST_STAGE'"));
  after(() => cy.task('deleteRelativePath', 'administrator/language/overrides/en-GB.override.ini'));

  it('can create a stage', () => {
    cy.visit('/administrator/index.php?option=com_workflow&task=stage.add&workflow_id=1&extension=com_content.article');
    cy.title().should('contain', 'Add Stage');
    cy.get('h1.page-title').should('contain', 'Add Stage');
    cy.get('#jform_title').clear().type('AUTOMATED_TEST_STAGE');
    cy.get('#jform_description').clear().type('automated test stage');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Item saved.');
    cy.title().should('contain', 'Edit Stage');
    cy.get('h1.page-title').should('contain', 'Edit Stage');
    cy.get('#jform_title').should('have.value', 'AUTOMATED_TEST_STAGE');
    cy.get('#stage_title_translation').should('have.value', 'Test stage translated');

    cy.clickToolbarButton('Cancel');
    cy.get('h1.page-title').should('contain', 'Stages: Basic Workflow');

    cy.get('table#stageList').contains('Test stage translated');
    cy.get('table#stageList').contains('automated test stage');
  });

  it('can edit a stage', () => {
    cy.db_createWorkflowStage({ title: 'Test stage', published: 0 }).then((stage) => {
      cy.visit(`/administrator/index.php?option=com_workflow&task=stage.edit&workflow_id=1&extension=com_content.article&id=${stage.id}`);
      cy.get('h1.page-title').should('contain', 'Edit Stage');
      cy.get('#jform_published').find(':selected').should('contain', 'Disabled');
      cy.get('#jform_published').select('Enabled');
      cy.get('#jform_description').clear().type('edited stage');
      cy.clickToolbarButton('Save');

      cy.checkForSystemMessage('Item saved.');
      cy.get('#jform_published').find(':selected').should('contain', 'Enabled');
      cy.clickToolbarButton('Save & Close');

      cy.checkForSystemMessage('Item saved.');
      cy.get('table#stageList').contains('edited stage');
    });
  });

  it('can edit a stage (list view)', () => {
    cy.db_createWorkflowStage({ title: 'Test stage' });
    cy.visit('/administrator/index.php?option=com_workflow&view=stages&workflow_id=1&extension=com_content.article');
    cy.get('table#stageList a').contains('Test stage').click();
    cy.get('h1.page-title').should('contain', 'Edit Stage');
    cy.get('#jform_title').clear().type('stage title');
    cy.get('#toolbar-dropdown-save-group .btn.btn-success.dropdown-toggle-split').click();
    cy.clickToolbarButton('Save & New');

    cy.checkForSystemMessage('Item saved.');
    cy.get('h1.page-title').should('contain', 'Add Stage');
  });

  it('redirects to the correct list view', () => {
    cy.visit('/administrator/index.php?option=com_workflow&task=stage.add&workflow_id=1&extension=com_content.article');
    cy.intercept('**/administrator/index.php?option=com_workflow&view=stages&workflow_id=1&extension=com_content.article').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
    cy.title().should('contain', 'Stages: Basic Workflow');
  });
});
