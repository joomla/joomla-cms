describe('Test in frontend that the newsfeeds category view', () => {
  it('can display a list of feeds in a menu item', () => {
    cy.db_createNewsFeed({ name: 'automated test feed 1' })
      .then((feed) => cy.db_createNewsFeed({ name: 'automated test feed 2', catid: feed.catid }))
      .then((feed) => cy.db_createNewsFeed({ name: 'automated test feed 3', catid: feed.catid }))
      .then((feed) => cy.db_createNewsFeed({ name: 'automated test feed 4', catid: feed.catid }))
      .then((feed) => cy.db_createMenuItem({ title: 'automated test feeds', link: `index.php?option=com_newsfeeds&view=category&id=${feed.catid}` }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test feeds)').click();

        cy.contains('automated test feed 1');
        cy.contains('automated test feed 2');
        cy.contains('automated test feed 3');
        cy.contains('automated test feed 4');
      });
  });

  it('can display a list of feeds without a menu item', () => {
    cy.db_createNewsFeed({ name: 'automated test feed 1' })
      .then((feed) => cy.db_createNewsFeed({ name: 'automated test feed 2', catid: feed.catid }))
      .then((feed) => cy.db_createNewsFeed({ name: 'automated test feed 3', catid: feed.catid }))
      .then((feed) => cy.db_createNewsFeed({ name: 'automated test feed 4', catid: feed.catid }))
      .then((feed) => {
        cy.visit(`/index.php?option=com_newsfeeds&view=category&id=${feed.catid}`);

        cy.contains('automated test feed 1');
        cy.contains('automated test feed 2');
        cy.contains('automated test feed 3');
        cy.contains('automated test feed 4');

        // HACK, muhme, June-30-2024 - DO NOT UP-MERGE, no problem in 5.1 upwards
        // Only avoided in System Tests, as the end of regular bugfix support for 4.x is 15 October 2024.
        // However, if you prefer a real solution in view with down-merge, create a PR or let me know.
        // To prevent: Warning: Attempt to read property "id" on null in
        //             /components/com_newsfeeds/src/View/Category/HtmlView.php on line 92
        // Workaround: Back to the home, as only the newsfeed view page contains the warning.
        cy.visit('/');
      });
  });
});
