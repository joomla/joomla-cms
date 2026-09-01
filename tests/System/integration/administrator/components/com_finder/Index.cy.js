describe('Test in backend that the Smart Search', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
  });
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__finder_links WHERE title IN ('Test article', 'Cafe Stereo', 'Café Stereo')");
    cy.task('queryDB', "DELETE FROM #__content WHERE title IN ('Test article', 'Cafe Stereo', 'Café Stereo')");
  });

  it('can index an article', () => {
    cy.db_createArticle({ title: 'Test article', alias: 'test-article' });
    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php finder:index`)
      .its('code')
      .should('equal', 0);

    cy.visit('/administrator/index.php?option=com_finder&view=index');
    cy.contains('Test article').should('exist');
  });

  it('can index articles with duplicate normalized terms', () => {
    cy.db_createArticle({ title: 'Café Stereo', alias: 'cafe-stereo-accent' });
    cy.db_createArticle({ title: 'Cafe Stereo', alias: 'cafe-stereo-no-accent' });

    cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php finder:index`)
      .then(({ code, stdout, stderr }) => {
        expect(code).to.equal(0);
        expect(stdout).to.contain('Total Processing Time');
        expect(stderr).to.equal('');
      });

    cy.task('queryDB', "SELECT COUNT(*) AS count FROM #__finder_links WHERE title IN ('Café Stereo', 'Cafe Stereo')").then((rows) => {
      expect(Number(rows[0].count)).to.equal(2);
    });
  });

  it('can index content', () => {
    // Create a new article
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article');
    cy.clickToolbarButton('Save & Close');
    // Visit the smart search page
    cy.visit('/administrator/index.php?option=com_finder&view=index');
    // Click the "Index" button. Note: with JDEBUG enabled the button is moved
    // into the #toolbar-indexing-group dropdown, see com_finder Index HtmlView.
    cy.get('#toolbar-index > button').click();
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
