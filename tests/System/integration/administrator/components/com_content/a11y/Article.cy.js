describe('Test accessibility on article create and edit', () => {
  const createTabs = [
    'Images and Links',
    'Options',
    'Schema',
    'Publishing',
    'Configure Edit Screen',
    'Permissions',
  ];

  beforeEach(() => {
    cy.doAdministratorLogin();
  });

  after(() => {
    cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Test article'");
  });

  // Create
  it('has no detectable a11y violations on create article initial view', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#toolbar-inlinehelp').should('be.visible').click();
    cy.get('#toolbar-dropdown-save-group > button').should('be.visible').click();
    cy.runAccessibilityScan();
  });

  // Tabs in create view
  createTabs.forEach((tab) => {
    it(`has no detectable a11y violations on create article view when ${tab} is clicked`, () => {
      cy.visit('/administrator/index.php?option=com_content&task=article.add');
      cy.contains('[role="tab"]', tab).should('be.visible').click();
      cy.runAccessibilityScan();
    });
  });

  it('has no detectable a11y violations on create article when Publishing tab is clicked and datepicker is expanded', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.contains('[role="tab"]', 'Publishing').should('be.visible').click();
    cy.get('#jform_publish_up_btn').should('be.visible').click();
    cy.get('.day').contains('17').click();
    cy.runAccessibilityScan();
  });

  it('has no detectable a11y violations on create article when Publishing tab is clicked and select user dialog is open', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.contains('[role="tab"]', 'Publishing').should('be.visible').click();
    cy.contains('Select User').should('be.visible').click();
    cy.get('joomla-dialog').should('be.visible');
    cy.runAccessibilityScan('joomla-dialog');
  });

  it('has no detectable a11y violations on create article when Permission tab is clicked and notes are expanded', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.contains('[role="tab"]', 'Permission').should('be.visible').click();
    cy.get('summary').should('be.visible').click();
    cy.runAccessibilityScan();
  });

  it('has no detectable a11y violations on create article when Save is clicked and Title is empty', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#toolbar-apply').should('be.visible').click();
    cy.runAccessibilityScan();
  });

  // Edit
  it('has no detectable a11y violations on edit article initial view', () => {
    cy.db_createArticle({ title: 'Test article' }).then((article) => {
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
      cy.runAccessibilityScan();
    });
  });

  it('has no detectable a11y violations on edit article view when Versions is clicked', () => {
    cy.db_createArticle({ title: 'Test article' }).then((article) => {
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
      cy.get('#toolbar-versions').should('be.visible').click();
      cy.get('joomla-dialog').should('be.visible');
      cy.runAccessibilityScan('joomla-dialog');
    });
  });

  it('has no detectable a11y violations on edit article view when Preview is clicked', () => {
    cy.db_createArticle({ title: 'Test article' }).then((article) => {
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
      cy.get('#toolbar-preview').should('be.visible').click();
      cy.get('#modal-preview').should('be.visible');
      cy.runAccessibilityScan('#modal-preview');
    });
  });

  it('has no detectable a11y violations on edit article view when Accessibility Check is clicked', () => {
    cy.db_createArticle({ title: 'Test article' }).then((article) => {
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
      cy.get('#toolbar-jooa11y-preview').should('be.visible').click();
      cy.get('#modal-jooa11y-preview').should('be.visible');
      cy.runAccessibilityScan('#modal-jooa11y-preview');
    });
  });
});
