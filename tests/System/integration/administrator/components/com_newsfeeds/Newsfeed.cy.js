describe('Test in backend that the newsfeed form', () => {
  beforeEach(() => cy.doAdministratorLogin());
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__category_item_map WHERE context = 'com_newsfeeds.newsfeed' AND item_id IN (SELECT id FROM #__newsfeeds WHERE name = 'Test newsfeed')");
    cy.task('queryDB', "DELETE FROM #__newsfeeds WHERE name = 'Test newsfeed'");
    cy.task('queryDB', "DELETE FROM #__categories WHERE title IN ('Test Newsfeed Primary Category', 'Test Newsfeed Additional Category')");
  });

  it('can create a newsfeed', () => {
    cy.visit('/administrator/index.php?option=com_newsfeeds&task=newsfeed.add');
    cy.get('#jform_name').clear().type('Test newsfeed');
    cy.get('#jform_link').clear().type('https://newsfeedtesturl');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('News feed saved.');
    cy.contains('Test newsfeed');
  });

  it('shows additional categories field', () => {
    cy.visit('/administrator/index.php?option=com_newsfeeds&task=newsfeed.add');

    cy.contains('label', 'Additional Categories').should('exist');
  });

  it('can assign additional categories to a newsfeed', () => {
    cy.db_createCategory({
      title: 'Test Newsfeed Primary Category',
      alias: 'test-newsfeed-primary-category',
      path: 'test-newsfeed-primary-category',
      extension: 'com_newsfeeds',
    }).then((primaryCategoryId) => cy.db_createCategory({
      title: 'Test Newsfeed Additional Category',
      alias: 'test-newsfeed-additional-category',
      path: 'test-newsfeed-additional-category',
      extension: 'com_newsfeeds',
    }).then((additionalCategoryId) => {
      cy.db_createNewsFeed({ name: 'Test newsfeed', link: 'https://newsfeedtesturl', catid: primaryCategoryId }).then((feed) => {
        cy.visit(`/administrator/index.php?option=com_newsfeeds&task=newsfeed.edit&id=${feed.id}`);
        cy.get('#jform_secondary_categories').select(`${additionalCategoryId}`, { force: true });
        cy.clickToolbarButton('Save & Close');

        cy.checkForSystemMessage('News feed saved.');
        cy.task('queryDB', `SELECT category_id FROM #__category_item_map WHERE context = 'com_newsfeeds.newsfeed' AND item_id = ${feed.id}`)
          .then((rows) => rows.map((row) => Number(row.category_id)))
          .should('include', additionalCategoryId);
      });
    }));
  });

  it('can change access level of a test newsfeed', () => {
    cy.db_createNewsFeed({ name: 'Test newsfeed', link: 'https://newsfeedtesturl' }).then((feed) => {
      cy.visit(`/administrator/index.php?option=com_newsfeeds&task=newsfeed.edit&id=${feed.id}`);
      cy.get('#jform_access').select('Special');
      cy.clickToolbarButton('Save & Close');

      cy.get('td').contains('Special').should('exist');
    });
  });

  it('check redirection to list view', () => {
    cy.visit('administrator/index.php?option=com_newsfeeds&task=newsfeed.add');
    cy.intercept('index.php?option=com_newsfeeds&view=newsfeeds').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
  });
});
