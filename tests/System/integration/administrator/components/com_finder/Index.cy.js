describe('Test in backend that the Smart Search', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
  });
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Test article'");
  });

  it('can index an article', () => {
    // Create a new article
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article');
    cy.clickToolbarButton('Save & Close');
    // Visit the smart search page
    cy.visit('/administrator/index.php?option=com_finder&view=index');
    cy.contains('Test article').should('exist');
  });

  it('can index content', () => {
    // Create a new article
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article');
    cy.clickToolbarButton('Save & Close');
    // Visit the smart search page
    cy.visit('/administrator/index.php?option=com_finder&view=index');
    // need different for j6
    cy.get('#toolbar-index > button').click();
    // Click the "Index" button
    cy.get('#indexing-group-children-index > button', { force: true }).click();
    cy.contains('Test article').should('exist');
  });

  it('can purge the index', () => {
    // Visit the smart search page
    cy.visit('/administrator/index.php?option=com_finder&view=index');
    cy.get('#toolbar-maintenance-group > button').click();
    // Click the "Clear Index" button
    cy.get('#maintenance-group-children-index-purge > button', { force: true }).click();
    cy.clickDialogConfirm(true);
    cy.checkForSystemMessage('All items have been deleted.');
  });
});
