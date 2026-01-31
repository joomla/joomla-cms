describe('Test that the featuring workflow plugin', () => {
  before(() => {
    cy.db_updateExtensionParameter('workflow_enabled', '1', 'com_content');
    cy.task('writeRelativeFile', {
      path: 'administrator/language/overrides/en-GB.override.ini',
      content: 'WORKFLOW_FEATURING="Workflow featuring"\nSTAGE_FEATURING="Stage featuring"\nTRANSITION_FEATURING_1="Transition featuring 1"\nTRANSITION_FEATURING_2="Transition featuring 2"\nTRANSITION_FEATURING_3="Transition featuring 3"\n',
    });
  });
  beforeEach(() => cy.doAdministratorLogin());
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__workflow_associations WHERE item_id IN (SELECT id FROM #__content WHERE title = 'Article featuring') AND extension = 'com_content.article'");
    cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Article featuring'");
    cy.task('queryDB', 'UPDATE #__workflows SET `default` = 1 WHERE id = 1');
  });
  after(() => {
    cy.db_updateExtensionParameter('workflow_enabled', '0', 'com_content');
    cy.db_updateExtensionParameter('forbiddenlist', '[]', 'plg_workflow_featuring');
    cy.task('deleteRelativePath', 'administrator/language/overrides/en-GB.override.ini');
  });

  it('can hide toolbar buttons', () => {
    cy.db_createArticle({ title: 'Test article', featured: 1 });
    cy.visit('/administrator/index.php?option=com_content&view=articles&filter[search]=Test%20article');
    cy.checkAllResults();
    cy.get('#toolbar-status-group').click().within(() => {
      cy.get('#status-group-children-featured').should('exist').should('not.be.visible');
      cy.get('#status-group-children-unfeatured').should('exist').should('not.be.visible');
    });
  });

  it('can block featured state change (list view)', () => {
    cy.db_createArticle({ title: 'Test article', featured: 0 }).then((article) => {
      cy.visit('/administrator/index.php?option=com_content&view=articles&filter[search]=Test%20article');
      cy.get('table#articleList .icon-unfeatured').parent('button').should('be.disabled');
      cy.window().then((win) => win.Joomla.getOptions('csrf.token')).then((token) => {
        cy.visit({
          url: '/administrator/index.php?option=com_content&view=articles',
          method: 'POST',
          body: {
            cid: [article.id],
            task: 'articles.featured',
            [token]: 1,
          },
        });
      });
      cy.checkForSystemMessage('You\'re not allowed to change the featured state of this item. Please use a workflow transition.');
    });
  });

  it('can block featured state change (edit form - backend)', () => {
    cy.db_createArticle({ title: 'Test article', featured: 0 }).then((article) => {
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
      cy.get('#jform_featured-lbl').should('contain', 'Featured: No');
      cy.intercept('POST', '**/administrator/index.php?option=com_content*', (req) => { req.body += '&jform[featured]=1'; }).as('articleSave');
      cy.clickToolbarButton('Save');
      cy.checkForSystemMessage('Save failed with the following error: You\'re not allowed to change the featured state of this item. Please use a workflow transition.');
      cy.get('#jform_featured-lbl').should('contain', 'Featured: No');
    });
  });

  it('can block featured state change (edit form - frontend)', () => {
    cy.db_createArticle({ title: 'Test article', featured: 0 }).then((article) => {
      cy.doFrontendLogin();
      cy.visit(`/index.php?option=com_content&task=article.edit&a_id=${article.id}`);
      cy.get('#jform_featured-lbl').should('contain', 'Featured: No');
      cy.intercept('POST', `**/index.php?view=form&layout=edit&a_id=${article.id}*`, (req) => { req.body += '&jform[featured]=1'; }).as('articleSave');
      cy.get('button[data-submit-task="article.apply"]').click();
      cy.checkForSystemMessage('Save failed with the following error: You\'re not allowed to change the featured state of this item. Please use a workflow transition.');
      cy.get('#jform_featured-lbl').should('contain', 'Featured: No');
    });
  });

  it('can modify transition edit form', () => {
    cy.db_createWorkflowTransition({ title: 'Test transition' }).then((transition) => {
      cy.visit(`/administrator/index.php?option=com_workflow&task=transition.edit&workflow_id=1&extension=com_content.article&id=${transition.id}`);
      cy.get('#myTab div[role="tablist"] button[aria-controls="attrib-actions"]').click();
      cy.get('#jform_options_featuring-lbl').should('contain', 'Featuring State');
      cy.get('#jform_options_featuring').should('exist');
      cy.get('#jform_options_featuring').select('Yes');
      cy.clickToolbarButton('Save');
      cy.checkForSystemMessage('Item saved.');
      cy.get('#jform_options_featuring').find(':selected').should('contain', 'Yes');
    });
  });

  it('can modify item edit form (backend)', () => {
    cy.db_createArticle({ title: 'Test article', featured: 1 }).then((article) => {
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
      cy.get('#jform_featured').should('not.exist');
      cy.get('#jform_featured-lbl').should('contain', 'Featured: Yes').find('span').should('have.class', 'text-success');
    });
    cy.db_createArticle({ title: 'Test article', featured: 0 }).then((article) => {
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
      cy.get('#jform_featured').should('not.exist');
      cy.get('#jform_featured-lbl').should('contain', 'Featured: No').find('span').should('have.class', 'text-danger');
    });
  });

  it('can modify item edit form (frontend)', () => {
    cy.doFrontendLogin();
    cy.db_createArticle({ title: 'Test article', featured: 1 }).then((article) => {
      cy.visit(`/index.php?option=com_content&task=article.edit&a_id=${article.id}`);
      cy.get('#com-content-form div[role="tablist"] button[aria-controls="publishing"]').click();
      cy.get('#jform_featured').should('not.exist');
      cy.get('#jform_featured-lbl').should('contain', 'Featured: Yes').find('span').should('have.class', 'text-success');
    });
    cy.db_createArticle({ title: 'Test article', featured: 0 }).then((article) => {
      cy.visit(`/index.php?option=com_content&task=article.edit&a_id=${article.id}`);
      cy.get('#com-content-form div[role="tablist"] button[aria-controls="publishing"]').click();
      cy.get('#jform_featured').should('not.exist');
      cy.get('#jform_featured-lbl').should('contain', 'Featured: No').find('span').should('have.class', 'text-danger');
    });
  });

  it('can change the featured state', () => {
    cy.db_createWorkflow({ title: 'WORKFLOW_FEATURING', extension: 'com_content.article', default: 1 }).then((workflow) => {
      cy.db_createWorkflowStage({ title: 'STAGE_FEATURING', workflow_id: workflow.id, default: 1 }).then((stage) => {
        cy.db_createWorkflowTransition({
          title: 'TRANSITION_FEATURING_1',
          workflow_id: workflow.id,
          to_stage_id: stage.id,
          options: { featuring: 0 },
        });
        cy.db_createWorkflowTransition({
          title: 'TRANSITION_FEATURING_2',
          workflow_id: workflow.id,
          to_stage_id: stage.id,
          options: { featuring: 1 },
        });
        cy.db_createWorkflowTransition({
          title: 'TRANSITION_FEATURING_3',
          workflow_id: workflow.id,
          to_stage_id: stage.id,
        });
      });
    });
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Article featuring');
    cy.get('#jform_transition').find(':selected').should('contain', 'Stage featuring');
    cy.get('#jform_transition').select('Transition featuring 1');
    cy.get('#jform_featured-lbl').should('contain', 'Featured: No');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Article saved.');
    cy.get('#jform_featured-lbl').should('contain', 'Featured: No');
    cy.get('#jform_transition').select('Transition featuring 2');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Article saved.');
    cy.get('#jform_featured-lbl').should('contain', 'Featured: Yes');
    cy.get('#jform_transition').select('Transition featuring 3');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Article saved.');
    cy.get('#jform_featured-lbl').should('contain', 'Featured: Yes');
    cy.get('#jform_transition').select('Transition featuring 1');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Article saved.');
    cy.get('#jform_featured-lbl').should('contain', 'Featured: No');
  });

  it('can exclude certain extensions', () => {
    cy.visit('/administrator/index.php?option=com_plugins&filter[folder]=workflow&filter[element]=featuring');
    cy.get('#pluginList a').contains('Workflow - Featuring').click();
    cy.selectOptionInFancySelect('#jform_params_forbiddenlist', 'Articles: Articles');
    cy.clickToolbarButton('Save & Close');

    cy.db_createArticle({ title: 'Test article', featured: 1 });
    cy.visit('/administrator/index.php?option=com_content&view=articles&filter[search]=Test%20article');
    cy.checkAllResults();
    cy.get('#toolbar-status-group').click().within(() => {
      cy.get('#status-group-children-featured').should('exist').should('be.visible');
      cy.get('#status-group-children-unfeatured').should('exist').should('be.visible').click();
    });

    cy.checkForSystemMessage('Article unfeatured.');
    cy.get('table#articleList .icon-unfeatured').click();

    cy.checkForSystemMessage('Article featured.');
    cy.get('table#articleList a').contains('Test article').click();
    cy.get('#jform_featured1').should('be.checked');
    cy.get('#jform_featured0').check();
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Article saved.');
    cy.get('#jform_featured0').should('be.checked');
  });
});
