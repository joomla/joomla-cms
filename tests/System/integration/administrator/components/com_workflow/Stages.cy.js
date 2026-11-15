describe('Test in backend that the stages list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_workflow&view=stages&workflow_id=1&extension=com_content.article&filter=');
  });
  after(() => cy.task('queryDB', 'UPDATE #__workflow_stages SET `default` = 1 WHERE id = 1'));

  it('can display a list of stages', () => {
    cy.title().should('contain', 'Stages: Basic Workflow');
    cy.get('h1.page-title').should('contain', 'Stages: Basic Workflow');
    cy.contains('Basic Stage');
  });

  it('can open the stage form', () => {
    cy.clickToolbarButton('New');

    cy.title().should('contain', 'Add Stage');
    cy.contains('Add Stage');
  });

  it('can enable the stage', () => {
    cy.db_createWorkflowStage({ title: 'Test stage', published: 0 });
    cy.reload();
    cy.setFilter('published', 'Unpublished');
    cy.searchForItem('Test stage');
    cy.checkAllResults();
    cy.clickToolbarButton('Action');
    cy.clickToolbarButton('Publish');

    cy.checkForSystemMessage('Stage enabled.');
  });

  it('can disable the stage', () => {
    cy.db_createWorkflowStage({ title: 'Test stage', published: 1 });
    cy.reload();
    cy.setFilter('published', 'Published');
    cy.searchForItem('Test stage');
    cy.checkAllResults();
    cy.clickToolbarButton('Action');
    cy.clickToolbarButton('Unpublish');

    cy.checkForSystemMessage('Stage disabled.');
  });

  it('can trash the stage', () => {
    cy.db_createWorkflowStage({ title: 'Test stage', published: 1 });
    cy.reload();
    cy.setFilter('published', 'Published');
    cy.searchForItem('Test stage');
    cy.checkAllResults();
    cy.clickToolbarButton('Action');
    cy.clickToolbarButton('Trash');

    cy.checkForSystemMessage('Stage trashed.');
  });

  it('can delete the stage', () => {
    cy.db_createWorkflowStage({ title: 'Test stage', published: -2 });
    cy.reload();
    cy.setFilter('published', 'Trashed');
    cy.searchForItem('Test stage');
    cy.checkAllResults();
    cy.clickToolbarButton('Empty Trash');
    cy.clickDialogConfirm(true);

    cy.checkForSystemMessage('Stage deleted.');
  });

  it('can checkin the stage', () => {
    cy.db_getUserId(Cypress.expose('username')).then((uid) => {
      cy.db_createWorkflowStage({ title: 'Test stage', checked_out: uid, checked_out_time: '2025-01-01 00:00:00' });
      cy.reload();
      cy.searchForItem('Test stage');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.clickToolbarButton('Check-In');

      cy.checkForSystemMessage('Stage checked in.');
    });
  });

  it('can enable the stage (grid button)', () => {
    cy.db_createWorkflowStage({ title: 'Test stage', published: 0, description: 'stage1' });
    cy.reload();
    cy.setFilter('published', 'Unpublished');
    cy.searchForItem('stage1');
    cy.get('table#stageList .icon-unpublish').click();

    cy.checkForSystemMessage('Stage enabled.');
  });

  it('can disable the stage (grid button)', () => {
    cy.db_createWorkflowStage({ title: 'Test stage', published: 1, description: 'stage2' });
    cy.reload();
    cy.setFilter('published', 'Published');
    cy.searchForItem('stage2');
    cy.get('table#stageList .icon-publish').click();

    cy.checkForSystemMessage('Stage disabled.');
  });

  it('can checkin the stage (grid button)', () => {
    cy.db_getUserId(Cypress.expose('username')).then((uid) => {
      cy.db_createWorkflowStage({ title: 'Test stage', checked_out: uid, checked_out_time: '2025-01-01 00:00:00' });
      cy.reload();
      cy.searchForItem('Test stage');
      cy.get('table#stageList .icon-checkedout').click();

      cy.checkForSystemMessage('Stage checked in.');
    });
  });

  it('can set stage as default (grid button)', () => {
    cy.db_createWorkflowStage({ title: 'Test stage', default: 0 });
    cy.reload();
    cy.searchForItem('Test stage');
    cy.get('table#stageList .icon-unfeatured').click();

    cy.checkForSystemMessage('Stage set as default.');
  });

  it('can filter state', () => {
    cy.db_createWorkflowStage({ title: 'Test stage 1', published: 1 });
    cy.db_createWorkflowStage({ title: 'Test stage 2', published: 0 });
    cy.db_createWorkflowStage({ title: 'Test stage 3', published: -2 });
    cy.reload();

    cy.get('#stageList')
      .should('contain', 'Test stage 1')
      .should('contain', 'Test stage 2')
      .should('not.contain', 'Test stage 3');

    cy.setFilter('published', 'Published');

    cy.get('#stageList')
      .should('contain', 'Test stage 1')
      .should('not.contain', 'Test stage 2')
      .should('not.contain', 'Test stage 3');

    cy.setFilter('published', 'Unpublished');

    cy.get('#stageList')
      .should('not.contain', 'Test stage 1')
      .should('contain', 'Test stage 2')
      .should('not.contain', 'Test stage 3');

    cy.setFilter('published', 'Trashed');

    cy.get('#stageList')
      .should('not.contain', 'Test stage 1')
      .should('not.contain', 'Test stage 2')
      .should('contain', 'Test stage 3');
  });
});
