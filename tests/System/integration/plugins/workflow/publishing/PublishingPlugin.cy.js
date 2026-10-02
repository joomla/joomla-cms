describe('Test that the publishing workflow plugin', () => {
  before(() => {
    cy.db_updateExtensionParameter('workflow_enabled', '1', 'com_content');
    cy.task('writeRelativeFile', {
      path: 'administrator/language/overrides/en-GB.override.ini',
      content: 'WORKFLOW_PUBLISHING="Workflow publishing"\nSTAGE_PUBLISHING="Stage publishing"\nTRANSITION_PUBLISHING_1="Transition publishing 1"\nTRANSITION_PUBLISHING_2="Transition publishing 2"\nTRANSITION_PUBLISHING_3="Transition publishing 3"\nTRANSITION_PUBLISHING_4="Transition publishing 4"\nTRANSITION_PUBLISHING_5="Transition publishing 5"\n',
    });
  });
  beforeEach(() => cy.doAdministratorLogin());
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__workflow_associations WHERE item_id IN (SELECT id FROM #__content WHERE title = 'Article publishing') AND extension = 'com_content.article'");
    cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Article publishing'");
    cy.task('queryDB', 'UPDATE #__workflows SET `default` = 1 WHERE id = 1');
  });
  after(() => {
    cy.db_updateExtensionParameter('workflow_enabled', '0', 'com_content');
    cy.db_updateExtensionParameter('forbiddenlist', '[]', 'plg_workflow_publishing');
    cy.task('deleteRelativePath', 'administrator/language/overrides/en-GB.override.ini');
  });

  it('can hide toolbar buttons', () => {
    cy.db_createArticle({ title: 'Test article', state: 1 });
    cy.visit('/administrator/index.php?option=com_content&view=articles&filter[search]=Test%20article');
    cy.checkAllResults();
    cy.get('#toolbar-status-group').click().within(() => {
      cy.get('#status-group-children-publish').should('exist').should('not.be.visible');
      cy.get('#status-group-children-unpublish').should('exist').should('not.be.visible');
      cy.get('#status-group-children-archive').should('exist').should('not.be.visible');
      cy.get('#status-group-children-trash').should('exist').should('not.be.visible');
    });
  });

  it('can block publishing state change (list view)', () => {
    cy.db_createArticle({ title: 'Test article', state: 0 }).then((article) => {
      cy.visit('/administrator/index.php?option=com_content&view=articles&filter[search]=Test%20article');
      cy.get('table#articleList .icon-unpublish').parent('button').should('be.disabled');
      cy.window().then((win) => win.Joomla.getOptions('csrf.token')).then((token) => {
        cy.visit({
          url: '/administrator/index.php?option=com_content&view=articles',
          method: 'POST',
          body: {
            cid: [article.id],
            task: 'articles.publish',
            [token]: 1,
          },
        });
      });
      cy.checkForSystemMessage('You\'re not allowed to change the publishing state of this item. Please use a workflow transition.');
    });
  });

  it('can block publishing state change (edit form - backend)', () => {
    cy.db_createArticle({ title: 'Test article', state: 0 }).then((article) => {
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
      cy.get('#jform_state-lbl').should('contain', 'Status: Unpublished');
      cy.intercept('POST', '**/administrator/index.php?option=com_content*', (req) => { req.body += '&jform[state]=1'; }).as('articleSave');
      cy.clickToolbarButton('Save');
      cy.checkForSystemMessage('Save failed with the following error: You\'re not allowed to change the publishing state of this item. Please use a workflow transition.');
      cy.get('#jform_state-lbl').should('contain', 'Status: Unpublished');
    });
  });

  it('can block publishing state change (edit form - frontend)', () => {
    cy.db_createArticle({ title: 'Test article', state: 0 }).then((article) => {
      cy.doFrontendLogin();
      cy.visit(`/index.php?option=com_content&task=article.edit&a_id=${article.id}`);
      cy.get('#jform_state-lbl').should('contain', 'Status: Unpublished');
      cy.intercept('POST', `**/index.php?view=form&layout=edit&a_id=${article.id}*`, (req) => { req.body += '&jform[state]=1'; }).as('articleSave');
      cy.get('button[data-submit-task="article.apply"]').click();
      cy.checkForSystemMessage('Save failed with the following error: You\'re not allowed to change the publishing state of this item. Please use a workflow transition.');
      cy.get('#jform_state-lbl').should('contain', 'Status: Unpublished');
    });
  });

  it('can modify transition edit form', () => {
    cy.db_createWorkflowTransition({ title: 'Test transition' }).then((transition) => {
      cy.visit(`/administrator/index.php?option=com_workflow&task=transition.edit&workflow_id=1&extension=com_content.article&id=${transition.id}`);
      cy.get('#myTab div[role="tablist"] button[aria-controls="attrib-actions"]').click();
      cy.get('#jform_options_publishing-lbl').should('contain', 'Publishing State');
      cy.get('#jform_options_publishing').should('exist');
      cy.get('#jform_options_publishing').select('Published');
      cy.clickToolbarButton('Save');
      cy.checkForSystemMessage('Item saved.');
      cy.get('#jform_options_publishing').find(':selected').should('contain', 'Published');
    });
  });

  it('can modify item edit form (backend)', () => {
    cy.db_createArticle({ title: 'Test article', state: 1 }).then((article) => {
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
      cy.get('#jform_state').should('not.exist');
      cy.get('#jform_state-lbl').should('contain', 'Status: Published').find('span').should('have.class', 'text-success');
    });
    cy.db_createArticle({ title: 'Test article', state: 0 }).then((article) => {
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
      cy.get('#jform_state').should('not.exist');
      cy.get('#jform_state-lbl').should('contain', 'Status: Unpublished').find('span').should('have.class', 'text-danger');
    });
    cy.db_createArticle({ title: 'Test article', state: -2 }).then((article) => {
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
      cy.get('#jform_state').should('not.exist');
      cy.get('#jform_state-lbl').should('contain', 'Status: Trashed').find('span').should('have.class', 'text-danger');
    });
    cy.db_createArticle({ title: 'Test article', state: 2 }).then((article) => {
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
      cy.get('#jform_state').should('not.exist');
      cy.get('#jform_state-lbl').should('contain', 'Status: Archived').find('span').should('have.class', 'text-body');
    });
  });

  it('can modify item edit form (frontend)', () => {
    cy.doFrontendLogin();
    cy.db_createArticle({ title: 'Test article', state: 1 }).then((article) => {
      cy.visit(`/index.php?option=com_content&task=article.edit&a_id=${article.id}`);
      cy.get('#com-content-form div[role="tablist"] button[aria-controls="publishing"]').click();
      cy.get('#jform_state').should('not.exist');
      cy.get('#jform_state-lbl').should('contain', 'Status: Published').find('span').should('have.class', 'text-success');
    });
    cy.db_createArticle({ title: 'Test article', state: 0 }).then((article) => {
      cy.visit(`/index.php?option=com_content&task=article.edit&a_id=${article.id}`);
      cy.get('#com-content-form div[role="tablist"] button[aria-controls="publishing"]').click();
      cy.get('#jform_state').should('not.exist');
      cy.get('#jform_state-lbl').should('contain', 'Status: Unpublished').find('span').should('have.class', 'text-danger');
    });
    cy.db_createArticle({ title: 'Test article', state: -2 }).then((article) => {
      cy.visit(`/index.php?option=com_content&task=article.edit&a_id=${article.id}`);
      cy.get('#com-content-form div[role="tablist"] button[aria-controls="publishing"]').click();
      cy.get('#jform_state').should('not.exist');
      cy.get('#jform_state-lbl').should('contain', 'Status: Trashed').find('span').should('have.class', 'text-danger');
    });
    cy.db_createArticle({ title: 'Test article', state: 2 }).then((article) => {
      cy.visit(`/index.php?option=com_content&task=article.edit&a_id=${article.id}`);
      cy.get('#com-content-form div[role="tablist"] button[aria-controls="publishing"]').click();
      cy.get('#jform_state').should('not.exist');
      cy.get('#jform_state-lbl').should('contain', 'Status: Archived').find('span').should('have.class', 'text-body');
    });
  });

  it('can change the publishing state', () => {
    cy.db_createWorkflow({ title: 'WORKFLOW_PUBLISHING', extension: 'com_content.article', default: 1 }).then((workflow) => {
      cy.db_createWorkflowStage({ title: 'STAGE_PUBLISHING', workflow_id: workflow.id, default: 1 }).then((stage) => {
        cy.db_createWorkflowTransition({
          title: 'TRANSITION_PUBLISHING_1',
          workflow_id: workflow.id,
          to_stage_id: stage.id,
          options: { publishing: 0 },
        });
        cy.db_createWorkflowTransition({
          title: 'TRANSITION_PUBLISHING_2',
          workflow_id: workflow.id,
          to_stage_id: stage.id,
          options: { publishing: 1 },
        });
        cy.db_createWorkflowTransition({
          title: 'TRANSITION_PUBLISHING_3',
          workflow_id: workflow.id,
          to_stage_id: stage.id,
        });
        cy.db_createWorkflowTransition({
          title: 'TRANSITION_PUBLISHING_4',
          workflow_id: workflow.id,
          to_stage_id: stage.id,
          options: { publishing: 2 },
        });
        cy.db_createWorkflowTransition({
          title: 'TRANSITION_PUBLISHING_5',
          workflow_id: workflow.id,
          to_stage_id: stage.id,
          options: { publishing: -2 },
        });
      });
    });
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Article publishing');
    cy.get('#jform_transition').find(':selected').should('contain', 'Stage publishing');
    cy.get('#jform_transition').select('Transition publishing 1');
    cy.get('#jform_state-lbl').should('contain', 'Status: Unpublished');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Article saved.');
    cy.get('#jform_state-lbl').should('contain', 'Status: Unpublished');
    cy.get('#jform_transition').select('Transition publishing 2');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Article saved.');
    cy.get('#jform_state-lbl').should('contain', 'Status: Published');
    cy.get('#jform_transition').select('Transition publishing 3');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Article saved.');
    cy.get('#jform_state-lbl').should('contain', 'Status: Published');
    cy.get('#jform_transition').select('Transition publishing 4');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Article saved.');
    cy.get('#jform_state-lbl').should('contain', 'Status: Archived');
    cy.get('#jform_transition').select('Transition publishing 5');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Article saved.');
    cy.get('#jform_state-lbl').should('contain', 'Status: Trashed');
    cy.get('#jform_transition').select('Transition publishing 1');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Article saved.');
    cy.get('#jform_state-lbl').should('contain', 'Status: Unpublished');
  });

  it('can exclude certain extensions', () => {
    cy.visit('/administrator/index.php?option=com_plugins&filter[folder]=workflow&filter[element]=publishing');
    cy.get('#pluginList a').contains('Workflow - Publishing').click();
    cy.selectOptionInFancySelect('#jform_params_forbiddenlist', 'Articles: Articles');
    cy.clickToolbarButton('Save & Close');

    cy.db_createArticle({ title: 'Test article', state: 0 });
    cy.visit('/administrator/index.php?option=com_content&view=articles&filter[search]=Test%20article');
    cy.checkAllResults();
    cy.get('#toolbar-status-group').click().within(() => {
      cy.get('#status-group-children-unpublish').should('exist').should('be.visible');
      cy.get('#status-group-children-archive').should('exist').should('be.visible');
      cy.get('#status-group-children-trash').should('exist').should('be.visible');
      cy.get('#status-group-children-publish').should('exist').should('be.visible').click();
    });

    cy.checkForSystemMessage('Article published.');
    cy.get('table#articleList .icon-publish').click();

    cy.checkForSystemMessage('Article unpublished.');
    cy.get('table#articleList a').contains('Test article').click();
    cy.get('#jform_state').find(':selected').should('contain', 'Unpublished');
    cy.get('#jform_state').select('Published');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Article saved.');
    cy.get('#jform_state').find(':selected').should('contain', 'Published');
  });
});
