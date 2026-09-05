describe('Test in backend that the content component', () => {
  beforeEach(() => cy.doAdministratorLogin());
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__content_frontpage WHERE content_id IN (SELECT id FROM #__content WHERE title = 'Test article')");
    cy.task('queryDB', "DELETE FROM #__workflow_associations WHERE item_id IN (SELECT id FROM #__content WHERE title = 'Test article') AND extension = 'com_content.article'");
    cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Test article'");
  });
  after(() => cy.db_updateExtensionParameter('workflow_enabled', '0', 'com_content'));

  it('can enable workflow integration', () => {
    cy.visit('/administrator/index.php?option=com_config&view=component&component=com_content');
    cy.get('#configTabs div[role="tablist"] button[aria-controls="integration"]').click();
    cy.get('#jform_workflow_enabled1').check();
    cy.intercept('index.php?option=com_config*').as('config_save');
    cy.clickToolbarButton('Save');
    cy.wait('@config_save');
    cy.checkForSystemMessage('Configuration saved.');
  });

  it('can run transition (unpublish)', () => {
    cy.db_createArticle({ title: 'Test article', state: 1 }).then(() => {
      cy.visit('/administrator/index.php?option=com_content&view=articles&filter[search]=Test%20article');
      cy.checkAllResults();
      cy.get('#toolbar-status-group').click().within(() => {
        cy.get('joomla-toolbar-button').contains('Unpublish').click();
      });
      cy.checkForSystemMessage('New state saved.');
      cy.get('#articleList tbody td.article-status span.icon-unpublish').should('exist');
    });
  });

  it('can run transition (publish)', () => {
    cy.db_createArticle({ title: 'Test article', state: 0 }).then(() => {
      cy.visit('/administrator/index.php?option=com_content&view=articles&filter[search]=Test%20article');
      cy.get('#cb0').check();
      cy.get('#toolbar-status-group').click().within(() => {
        cy.get('joomla-toolbar-button').contains('Publish').click();
      });
      cy.checkForSystemMessage('New state saved.');
      cy.get('#articleList tbody td.article-status span.icon-publish').should('exist');
    });
  });

  it('can run transition (trash)', () => {
    cy.db_createArticle({ title: 'Test article', state: 0 }).then(() => {
      cy.visit('/administrator/index.php?option=com_content&view=articles&filter[search]=Test%20article&filter[published]=*');
      cy.get('#cb0').check();
      cy.get('#toolbar-status-group').click().within(() => {
        cy.get('joomla-toolbar-button').contains('Trash').click();
      });
      cy.checkForSystemMessage('New state saved.');
      cy.get('#articleList tbody td.article-status span.icon-trash').should('exist');
    });
  });

  it('can run transition (archive)', () => {
    cy.db_createArticle({ title: 'Test article', state: 1 }).then(() => {
      cy.visit('/administrator/index.php?option=com_content&view=articles&filter[search]=Test%20article&filter[published]=*');
      cy.get('#articleList thead th').contains('Stage');
      cy.get('#articleList tbody td.article-status span.icon-publish').should('exist');
      cy.get('td.article-stage button').click();
      cy.get('td.article-stage select').select('Archive');
      cy.checkForSystemMessage('New state saved.');
      cy.get('#articleList tbody td.article-status span.icon-archive').should('exist');
    });
  });

  it('can run transition (feature)', () => {
    cy.db_createArticle({ title: 'Test article', state: 1 }).then(() => {
      cy.visit('/administrator/index.php?option=com_content&view=articles&filter[search]=Test%20article');
      cy.get('#articleList thead th').contains('Stage');
      cy.get('#articleList tbody td span.icon-unfeatured').should('exist');
      cy.get('td.article-stage button').click();
      cy.get('td.article-stage select').select('Feature');
      cy.checkForSystemMessage('New state saved.');
      cy.get('#articleList tbody td span.icon-color-featured.icon-star').should('exist');
    });
  });

  it('can run transition (publish & feature)', () => {
    cy.db_createArticle({ title: 'Test article', state: 0 }).then(() => {
      cy.visit('/administrator/index.php?option=com_content&view=articles&filter[search]=Test%20article');
      cy.get('#articleList thead th').contains('Stage');
      cy.get('#articleList tbody td.article-status span.icon-unpublish').should('exist');
      cy.get('#articleList tbody td span.icon-unfeatured').should('exist');
      cy.get('td.article-stage button').click();
      cy.get('td.article-stage select').select('Publish & Feature');
      cy.checkForSystemMessage('New state saved.');
      cy.get('#articleList tbody td.article-status span.icon-publish').should('exist');
      cy.get('#articleList tbody td span.icon-color-featured.icon-star').should('exist');
    });
  });

  it('can create new article and run transitions', () => {
    cy.visit('/administrator/index.php?option=com_content&view=articles&filter=');
    cy.clickToolbarButton('New');
    cy.get('#jform_title').type('Test article');
    cy.get('#jform_state-lbl').contains('Unpublished');
    cy.get('#jform_transition').select('Publish');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Article saved.');
    cy.get('#jform_state-lbl').contains('Published');
    cy.get('#jform_featured-lbl').contains('No');
    cy.get('#jform_transition').select('Feature');
    cy.clickToolbarButton('Save');

    cy.checkForSystemMessage('Article saved.');
    cy.get('#jform_state-lbl').contains('Published');
    cy.get('#jform_featured-lbl').contains('Yes');
  });
});
