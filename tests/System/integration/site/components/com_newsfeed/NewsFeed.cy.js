describe('Test in frontend that the newsfeeds details view', () => {
  ['joomla.org'].forEach((file) => {
    it(`can display a feed in a menu item from ${file}`, () => {
      cy.db_createNewsFeed({ name: 'automated test feed 1', link: `${Cypress.config('baseUrl')}/tests/System/data/com_newsfeeds/${file}.xml` })
        .then((feed) => cy.db_createMenuItem({ title: 'automated test feeds', link: `index.php?option=com_newsfeeds&view=newsfeed&id=${feed.id}` }))
        .then(() => {
          cy.visit('/');
          cy.get('a:contains(automated test feed)').click();

          cy.contains('automated test feed 1');
          cy.get('.com-newsfeeds-newsfeed__items').should('exist');
          cy.get('.com-newsfeeds-newsfeed__items').children().should('have.length', 5);
        });
    });

    it(`can display a feed without a menu item from ${file}`, () => {
      cy.db_createNewsFeed({ name: 'automated test feed 1', link: `${Cypress.config('baseUrl')}/tests/System/data/com_newsfeeds/${file}.xml` })
        .then((feed) => {
          cy.visit(`/index.php?option=com_newsfeeds&view=newsfeed&id=${feed.id}`);

          cy.contains('automated test feed 1');
          cy.get('.com-newsfeeds-newsfeed__items').should('exist');
          cy.get('.com-newsfeeds-newsfeed__items').children().should('have.length', 5);
        });
    });
  });
});
