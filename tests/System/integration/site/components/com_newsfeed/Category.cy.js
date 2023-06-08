describe('Test in frontend that the newsfeeds category view', () => {
  it('can display a list of feeds in a menu item', () => {
    cy.db_createNewsFeed({ name: 'automated test feed 1' })
      .then(() => cy.db_createNewsFeed({ name: 'automated test feed 2' }))
      .then(() => cy.db_createNewsFeed({ name: 'automated test feed 3' }))
      .then(() => cy.db_createNewsFeed({ name: 'automated test feed 4' }))
      .then(() => cy.db_createMenuItem({ title: 'automated test feeds', link: 'index.php?option=com_newsfeeds&view=category&id=5' }))
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
      .then(() => cy.db_createNewsFeed({ name: 'automated test feed 2' }))
      .then(() => cy.db_createNewsFeed({ name: 'automated test feed 3' }))
      .then(() => cy.db_createNewsFeed({ name: 'automated test feed 4' }))
      .then(() => {
        cy.visit('/index.php?option=com_newsfeeds&view=category&id=5');

        cy.contains('automated test feed 1');
        cy.contains('automated test feed 2');
        cy.contains('automated test feed 3');
        cy.contains('automated test feed 4');
      });
  });
});
