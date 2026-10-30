describe('Test in backend that the workflows list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_workflow&view=workflows&extension=com_content.article&filter=');
  });
  after(() => cy.task('queryDB', 'UPDATE #__workflows SET `default` = 1 WHERE id = 1'));

  it('can display a list of workflows', () => {
    cy.title().should('contain', 'Workflows');
    cy.get('h1.page-title').should('contain', 'Workflows');
    cy.contains('Basic Workflow');
  });

  it('can open the workflow form', () => {
    cy.clickToolbarButton('New');

    cy.title().should('contain', 'Add Workflow');
    cy.contains('Add Workflow');
  });

  it('can enable the workflow', () => {
    cy.db_createWorkflow({ title: 'Test workflow', extension: 'com_content.article', published: 0 });
    cy.reload();
    cy.setFilter('published', 'Unpublished');
    cy.searchForItem('Test workflow');
    cy.checkAllResults();
    cy.clickToolbarButton('Action');
    cy.clickToolbarButton('Publish');

    cy.checkForSystemMessage('Workflow enabled.');
  });

  it('can disable the workflow', () => {
    cy.db_createWorkflow({ title: 'Test workflow', extension: 'com_content.article', published: 1 });
    cy.reload();
    cy.setFilter('published', 'Published');
    cy.searchForItem('Test workflow');
    cy.checkAllResults();
    cy.clickToolbarButton('Action');
    cy.clickToolbarButton('Unpublish');

    cy.checkForSystemMessage('Workflow disabled.');
  });

  it('can trash the workflow', () => {
    cy.db_createWorkflow({ title: 'Test workflow', extension: 'com_content.article', published: 1 });
    cy.reload();
    cy.setFilter('published', 'Published');
    cy.searchForItem('Test workflow');
    cy.checkAllResults();
    cy.clickToolbarButton('Action');
    cy.clickToolbarButton('Trash');

    cy.checkForSystemMessage('Workflow trashed.');
  });

  it('can delete the workflow', () => {
    cy.db_createWorkflow({ title: 'Test workflow', extension: 'com_content.article', published: -2 });
    cy.reload();
    cy.setFilter('published', 'Trashed');
    cy.searchForItem('Test workflow');
    cy.checkAllResults();
    cy.clickToolbarButton('Empty Trash');
    cy.clickDialogConfirm(true);

    cy.checkForSystemMessage('Workflow deleted.');
  });

  it('can checkin the workflow', () => {
    cy.db_getUserId(Cypress.env('username')).then((uid) => {
      cy.db_createWorkflow({
        title: 'Test workflow',
        extension: 'com_content.article',
        checked_out: uid,
        checked_out_time: '2025-01-01 00:00:00',
      });
      cy.reload();
      cy.searchForItem('Test workflow');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.clickToolbarButton('Check-In');

      cy.checkForSystemMessage('Workflow checked in.');
    });
  });

  it('can enable the workflow (grid button)', () => {
    cy.db_createWorkflow({
      title: 'Test workflow',
      extension: 'com_content.article',
      published: 0,
      description: 'workflow1',
    });
    cy.reload();
    cy.setFilter('published', 'Unpublished');
    cy.searchForItem('workflow1');
    cy.get('table#workflowList .icon-unpublish').click();

    cy.checkForSystemMessage('Workflow enabled.');
  });

  it('can disable the workflow (grid button)', () => {
    cy.db_createWorkflow({
      title: 'Test workflow',
      extension: 'com_content.article',
      published: 1,
      description: 'workflow2',
    });
    cy.reload();
    cy.setFilter('published', 'Published');
    cy.searchForItem('workflow2');
    cy.get('table#workflowList .icon-publish').click();

    cy.checkForSystemMessage('Workflow disabled.');
  });

  it('can checkin the workflow (grid button)', () => {
    cy.db_getUserId(Cypress.env('username')).then((uid) => {
      cy.db_createWorkflow({
        title: 'Test workflow',
        extension: 'com_content.article',
        checked_out: uid,
        checked_out_time: '2025-01-01 00:00:00',
      });
      cy.reload();
      cy.searchForItem('Test workflow');
      cy.get('table#workflowList .icon-checkedout').click();

      cy.checkForSystemMessage('Workflow checked in.');
    });
  });

  it('can set workflow as default (grid button)', () => {
    cy.db_createWorkflow({ title: 'Test workflow', extension: 'com_content.article', default: 0 });
    cy.reload();
    cy.searchForItem('Test workflow');
    cy.get('table#workflowList .icon-unfeatured').click();

    cy.checkForSystemMessage('Workflow set as default.');
  });

  it('can filter state', () => {
    cy.db_createWorkflow({ title: 'Test workflow 1', extension: 'com_content.article', published: 1 });
    cy.db_createWorkflow({ title: 'Test workflow 2', extension: 'com_content.article', published: 0 });
    cy.db_createWorkflow({ title: 'Test workflow 3', extension: 'com_content.article', published: -2 });
    cy.reload();

    cy.get('#workflowList')
      .should('contain', 'Test workflow 1')
      .should('contain', 'Test workflow 2')
      .should('not.contain', 'Test workflow 3');

    cy.setFilter('published', 'Published');

    cy.get('#workflowList')
      .should('contain', 'Test workflow 1')
      .should('not.contain', 'Test workflow 2')
      .should('not.contain', 'Test workflow 3');

    cy.setFilter('published', 'Unpublished');

    cy.get('#workflowList')
      .should('not.contain', 'Test workflow 1')
      .should('contain', 'Test workflow 2')
      .should('not.contain', 'Test workflow 3');

    cy.setFilter('published', 'Trashed');

    cy.get('#workflowList')
      .should('not.contain', 'Test workflow 1')
      .should('not.contain', 'Test workflow 2')
      .should('contain', 'Test workflow 3');
  });

  it('can open list of stages', () => {
    cy.searchForItem('BASIC WORKFLOW');
    cy.intercept('**/administrator/index.php?option=com_workflow&view=workflow*').as('workflows');
    cy.intercept('**/administrator/index.php?option=com_workflow&view=stages*').as('stages');
    cy.get('table#workflowList a.btn.btn-warning').click();

    cy.wait('@stages');
    cy.get('h1.page-title').should('contain', 'Stages: Basic Workflow');
    cy.get('#toolbar-link').click();

    cy.wait('@workflows');
    cy.get('h1.page-title').should('contain', 'Workflows');
  });

  it('can open list of transitions', () => {
    cy.searchForItem('BASIC WORKFLOW');
    cy.intercept('**/administrator/index.php?option=com_workflow&view=workflow*').as('workflows');
    cy.intercept('**/administrator/index.php?option=com_workflow&view=transitions*').as('transitions');
    cy.get('table#workflowList a.btn.btn-primary').click();

    cy.wait('@transitions');
    cy.get('h1.page-title').should('contain', 'Transitions: Basic Workflow');
    cy.get('#toolbar-link').click();

    cy.wait('@workflows');
    cy.get('h1.page-title').should('contain', 'Workflows');
  });

  it('can visit options', () => {
    cy.intercept('**/administrator/index.php?option=com_config&view=component&component=com_content*').as('options');
    cy.intercept('**/administrator/index.php?option=com_workflow&view=workflow*').as('listview');

    cy.clickToolbarButton('Options');
    cy.wait('@options');
    cy.get('h1.page-title').should('contain', 'Articles: Options');

    cy.clickToolbarButton('Cancel');
    cy.wait('@listview');
    cy.get('h1.page-title').should('contain', 'Workflows');
  });
});
