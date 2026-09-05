describe('Test in backend that the transition form', () => {
  before(() => cy.task('writeRelativeFile', { path: 'administrator/language/overrides/en-GB.override.ini', content: 'AUTOMATED_TEST_TRANSITION="Test transition translated"' }));
  beforeEach(() => cy.doAdministratorLogin());
  afterEach(() => cy.task('queryDB', "DELETE FROM #__workflow_transitions WHERE title = 'AUTOMATED_TEST_TRANSITION'"));
  after(() => cy.task('deleteRelativePath', 'administrator/language/overrides/en-GB.override.ini'));

  it('can create a transition', () => {
    cy.visit('/administrator/index.php?option=com_workflow&task=transition.add&workflow_id=1&extension=com_content.article');
    cy.title().should('contain', 'Add Transition');
    cy.get('h1.page-title').should('contain', 'Add Transition');
    cy.get('#jform_title').clear().type('AUTOMATED_TEST_TRANSITION');
    cy.get('#jform_description').clear().type('automated test transition');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Item saved.');
    cy.title().should('contain', 'Edit Transition');
    cy.get('h1.page-title').should('contain', 'Edit Transition');
    cy.get('#jform_title').should('have.value', 'AUTOMATED_TEST_TRANSITION');
    // cy.get('#transition_title_translation').should('have.value', 'Test transition translated');

    cy.clickToolbarButton('Cancel');
    cy.get('h1.page-title').should('contain', 'Transitions: Basic Workflow');

    cy.get('table#transitionList').contains('Test transition translated');
    cy.get('table#transitionList').contains('automated test transition');
  });

  it('can edit a transition', () => {
    cy.db_createWorkflowTransition({ title: 'Test transition', published: 0 }).then((transition) => {
      cy.visit(`/administrator/index.php?option=com_workflow&task=transition.edit&workflow_id=1&extension=com_content.article&id=${transition.id}`);
      cy.get('h1.page-title').should('contain', 'Edit Transition');
      cy.get('#jform_published').find(':selected').should('contain', 'Disabled');
      cy.get('#jform_published').select('Enabled');
      cy.get('#jform_description').clear().type('edited transition');
      cy.clickToolbarButton('Save');

      cy.checkForSystemMessage('Item saved.');
      cy.get('#jform_published').find(':selected').should('contain', 'Enabled');
      cy.clickToolbarButton('Save & Close');

      cy.checkForSystemMessage('Item saved.');
      cy.get('table#transitionList').contains('edited transition');
    });
  });

  it('can edit a transition (list view)', () => {
    cy.db_createWorkflowTransition({ title: 'Test transition' });
    cy.visit('/administrator/index.php?option=com_workflow&view=transitions&workflow_id=1&extension=com_content.article');
    cy.get('table#transitionList a').contains('Test transition').click();
    cy.get('h1.page-title').should('contain', 'Edit Transition');
    cy.get('#jform_title').clear().type('transition title');
    cy.get('#toolbar-dropdown-save-group .btn.btn-success.dropdown-toggle-split').click();
    cy.clickToolbarButton('Save & New');

    cy.checkForSystemMessage('Item saved.');
    cy.get('h1.page-title').should('contain', 'Add Transition');
  });

  it('redirects to the correct list view', () => {
    cy.visit('/administrator/index.php?option=com_workflow&task=transition.add&workflow_id=1&extension=com_content.article');
    cy.intercept('**/administrator/index.php?option=com_workflow&view=transitions&workflow_id=1&extension=com_content.article').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
    cy.title().should('contain', 'Transitions: Basic Workflow');
  });
});
