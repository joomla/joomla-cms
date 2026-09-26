describe('Test in frontend that the feed module', () => {
  ['joomla.org'].forEach((file) => {
    it('can display feed', () => {
      cy.db_createModule({
        title: 'automated test feed',
        module: 'mod_feed',
        params: `{"rssurl": "${Cypress.config('baseUrl').replace('https://', 'http://')}/tests/System/data/com_newsfeeds/${file}.xml" }`,
      })
        .then(() => {
          cy.visit('/');

          cy.contains('automated test feed');
          cy.get('ul.newsfeed').should('exist');
          cy.get('ul.newsfeed').children().should('have.length', 3);
        });
    });
  });
});
