describe('Test in frontend that the newsfeeds categories view', () => {
  it('can display a list of categories', () => {
    cy.db_createCategory({ title: 'automated test category 1', extension: 'com_newsfeeds' })
      .then((id) => cy.db_createNewsFeed({ name: 'automated test feed 1', catid: id }))
      .then(() => cy.db_createCategory({ title: 'automated test category 2', extension: 'com_newsfeeds' }))
      .then(async (id) => {
        await cy.db_createNewsFeed({ name: 'automated test feed 2', catid: id });
        await cy.db_createNewsFeed({ name: 'automated test feed 3', catid: id });
      })
      .then(() => {
        cy.visit('/index.php?option=com_newsfeeds&view=categories');

        cy.contains('automated test category 1');
        cy.contains('automated test category 2');
        cy.get(':nth-child(2) > .page-header > .badge').contains('# News feeds 1');
        cy.get(':nth-child(3) > .page-header > .badge').contains('# News feeds 2');
      });
  });
});
