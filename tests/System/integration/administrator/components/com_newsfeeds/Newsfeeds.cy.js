describe('Test in backend that the newsfeeds list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_newsfeeds&view=newsfeeds&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'News Feeds');
  });

  it('can display a list of newsfeeds', () => {
    cy.db_createNewsFeed({ name: 'Test newsfeed' }).then(() => {
      cy.reload();

      cy.contains('Test newsfeed');
    });
  });

  it('can open the newsfeed form', () => {
    cy.clickToolbarButton('New');

    cy.contains('News Feeds: New');
  });

  it('can publish the test newsfeed', () => {
    cy.db_createNewsFeed({ name: 'Test newsfeed', link: 'https://newsfeedtesturl', published: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test newsfeed');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Publish').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('News feed published.').should('exist');
    });
  });

  it('can unpublish the test newsfeed', () => {
    cy.db_createNewsFeed({ name: 'Test newsfeed', link: 'https://newsfeedtesturl', published: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test newsfeed');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('News feed unpublished.').should('exist');
    });
  });

  it('can trash the test newsfeed', () => {
    cy.db_createNewsFeed({ name: 'Test newsfeed', link: 'https://newsfeedtesturl' }).then(() => {
      cy.reload();
      cy.searchForItem('Test newsfeed');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('News feed trashed.').should('exist');
    });
  });

  it('can delete the test newsfeed', () => {
    cy.db_createNewsFeed({ name: 'Test newsfeed', link: 'https://newsfeedtesturl', published: -2 }).then(() => {
      cy.reload();
      cy.setFilter('published', 'Trashed');
      cy.searchForItem('Test newsfeed');
      cy.checkAllResults();
      cy.clickToolbarButton('empty trash');
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('News feed deleted.').should('exist');
    });
  });
});
