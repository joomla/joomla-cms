describe('Test that the feed view ', () => {
  it('can display a feed in menu item', () => {
    cy.db_createNewsfeed({ name: 'automated test feed 1', link: 'https://www.joomla.org/announcements.feed?type=rss' })
      .then((id) => cy.db_createMenuItem({ title: 'automated test feeds', link: `index.php?option=com_newsfeeds&view=newsfeed&id=${id}` }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test feed)').click();

        cy.contains('automated test feed 1');
        cy.get('.com-newsfeeds-newsfeed__items').should('exist');
      });
  });

  it('can display a list of feeds without menu item', () => {
    cy.db_createNewsfeed({ name: 'automated test feed 1', link: 'https://www.joomla.org/announcements.feed?type=rss' })
      .then((id) => {
        cy.visit(`index.php?option=com_newsfeeds&view=newsfeed&id=${id}`);

        cy.contains('automated test feed 1');
        cy.get('.com-newsfeeds-newsfeed__items').should('exist');
      });
  });
});
