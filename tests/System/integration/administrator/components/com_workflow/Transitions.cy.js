describe('Test in backend that the transitions list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_workflow&view=transitions&workflow_id=1&extension=com_content.article&filter=');
  });

  it('can display a list of transitions', () => {
    cy.title().should('contain', 'Transitions: Basic Workflow');
    cy.get('h1.page-title').should('contain', 'Transitions: Basic Workflow');
    cy.contains('Publish');
  });

  it('can open the transition form', () => {
    cy.clickToolbarButton('New');

    cy.title().should('contain', 'Add Transition');
    cy.contains('Add Transition');
  });

  it('can enable the transition', () => {
    cy.db_createWorkflowTransition({ title: 'Test transition', published: 0 });
    cy.reload();
    cy.setFilter('published', 'Unpublished');
    cy.searchForItem('Test transition');
    cy.checkAllResults();
    cy.clickToolbarButton('Action');
    cy.clickToolbarButton('Publish');

    cy.checkForSystemMessage('Transition enabled.');
  });

  it('can disable the transition', () => {
    cy.db_createWorkflowTransition({ title: 'Test transition', published: 1 });
    cy.reload();
    cy.setFilter('published', 'Published');
    cy.searchForItem('Test transition');
    cy.checkAllResults();
    cy.clickToolbarButton('Action');
    cy.clickToolbarButton('Unpublish');

    cy.checkForSystemMessage('Transition disabled.');
  });

  it('can trash the transition', () => {
    cy.db_createWorkflowTransition({ title: 'Test transition', published: 1 });
    cy.reload();
    cy.setFilter('published', 'Published');
    cy.searchForItem('Test transition');
    cy.checkAllResults();
    cy.clickToolbarButton('Action');
    cy.clickToolbarButton('Trash');

    cy.checkForSystemMessage('Transition trashed.');
  });

  it('can delete the transition', () => {
    cy.db_createWorkflowTransition({ title: 'Test transition', published: -2 });
    cy.reload();
    cy.setFilter('published', 'Trashed');
    cy.searchForItem('Test transition');
    cy.checkAllResults();
    cy.clickToolbarButton('Empty Trash');
    cy.clickDialogConfirm(true);

    cy.checkForSystemMessage('Transition deleted.');
  });

  it('can checkin the transition', () => {
    cy.db_getUserId(Cypress.env('username')).then((uid) => {
      cy.db_createWorkflowTransition({ title: 'Test transition', checked_out: uid, checked_out_time: '2025-01-01 00:00:00' });
      cy.reload();
      cy.searchForItem('Test transition');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.clickToolbarButton('Check-In');

      cy.checkForSystemMessage('Transition checked in.');
    });
  });

  it('can enable the transition (grid button)', () => {
    cy.db_createWorkflowTransition({ title: 'Test transition', published: 0, description: 'transition1' });
    cy.reload();
    cy.setFilter('published', 'Unpublished');
    cy.searchForItem('transition1');
    cy.get('table#transitionList .icon-unpublish').click();

    cy.checkForSystemMessage('Transition enabled.');
  });

  it('can disable the transition (grid button)', () => {
    cy.db_createWorkflowTransition({ title: 'Test transition', published: 1, description: 'transition2' });
    cy.reload();
    cy.setFilter('published', 'Published');
    cy.searchForItem('transition2');
    cy.get('table#transitionList .icon-publish').click();

    cy.checkForSystemMessage('Transition disabled.');
  });

  it('can checkin the transition (grid button)', () => {
    cy.db_getUserId(Cypress.env('username')).then((uid) => {
      cy.db_createWorkflowTransition({ title: 'Test transition', checked_out: uid, checked_out_time: '2025-01-01 00:00:00' });
      cy.reload();
      cy.searchForItem('Test transition');
      cy.get('table#transitionList .icon-checkedout').click();

      cy.checkForSystemMessage('Transition checked in.');
    });
  });

  it('can filter state', () => {
    cy.db_createWorkflowTransition({ title: 'Test transition 1', published: 1 });
    cy.db_createWorkflowTransition({ title: 'Test transition 2', published: 0 });
    cy.db_createWorkflowTransition({ title: 'Test transition 3', published: -2 });
    cy.reload();

    cy.get('#transitionList')
      .should('contain', 'Test transition 1')
      .should('contain', 'Test transition 2')
      .should('not.contain', 'Test transition 3');

    cy.setFilter('published', 'Published');

    cy.get('#transitionList')
      .should('contain', 'Test transition 1')
      .should('not.contain', 'Test transition 2')
      .should('not.contain', 'Test transition 3');

    cy.setFilter('published', 'Unpublished');

    cy.get('#transitionList')
      .should('not.contain', 'Test transition 1')
      .should('contain', 'Test transition 2')
      .should('not.contain', 'Test transition 3');

    cy.setFilter('published', 'Trashed');

    cy.get('#transitionList')
      .should('not.contain', 'Test transition 1')
      .should('not.contain', 'Test transition 2')
      .should('contain', 'Test transition 3');
  });

  it('can filter current stage', () => {
    cy.db_createWorkflowTransition({ title: 'Test transition 1' });
    cy.db_createWorkflowStage({ title: 'Test stage 2' }).then((stage) => cy.db_createWorkflowTransition({ title: 'Test transition 2', from_stage_id: stage.id }));
    cy.db_createWorkflowStage({ title: 'Test stage 3' }).then((stage) => cy.db_createWorkflowTransition({ title: 'Test transition 3', from_stage_id: stage.id }));
    cy.reload();

    cy.get('#transitionList')
      .should('contain', 'Test transition 1')
      .should('contain', 'Test transition 2')
      .should('contain', 'Test transition 3');

    cy.setFilter('from_stage', 'Test stage 2');

    cy.get('#transitionList')
      .should('not.contain', 'Test transition 1')
      .should('contain', 'Test transition 2')
      .should('not.contain', 'Test transition 3');

    cy.setFilter('from_stage', 'Test stage 3');

    cy.get('#transitionList')
      .should('not.contain', 'Test transition 1')
      .should('not.contain', 'Test transition 2')
      .should('contain', 'Test transition 3');
  });

  it('can filter target stage', () => {
    cy.db_createWorkflowTransition({ title: 'Test transition 1' });
    cy.db_createWorkflowStage({ title: 'Test stage 2' }).then((stage) => cy.db_createWorkflowTransition({ title: 'Test transition 2', to_stage_id: stage.id }));
    cy.db_createWorkflowStage({ title: 'Test stage 3' }).then((stage) => cy.db_createWorkflowTransition({ title: 'Test transition 3', to_stage_id: stage.id }));
    cy.reload();

    cy.get('#transitionList')
      .should('contain', 'Test transition 1')
      .should('contain', 'Test transition 2')
      .should('contain', 'Test transition 3');

    cy.setFilter('to_stage', 'Test stage 2');

    cy.get('#transitionList')
      .should('not.contain', 'Test transition 1')
      .should('contain', 'Test transition 2')
      .should('not.contain', 'Test transition 3');

    cy.setFilter('to_stage', 'Test stage 3');

    cy.get('#transitionList')
      .should('not.contain', 'Test transition 1')
      .should('not.contain', 'Test transition 2')
      .should('contain', 'Test transition 3');
  });
});
