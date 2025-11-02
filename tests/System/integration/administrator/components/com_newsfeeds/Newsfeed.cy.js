describe('Test in backend that the newsfeed form', () => {
  beforeEach(() => cy.doAdministratorLogin());
  afterEach(() => cy.task('queryDB', "DELETE FROM #__newsfeeds WHERE name = 'Test newsfeed'"));

  it('can create a newsfeed', () => {
    cy.visit('/administrator/index.php?option=com_newsfeeds&task=newsfeed.add');
    cy.get('#jform_name').clear().type('Test newsfeed');
    cy.get('#jform_link').clear().type('https://newsfeedtesturl');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('News feed saved.');
    cy.contains('Test newsfeed');
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
