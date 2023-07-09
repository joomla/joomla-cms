describe('Test that the articles category menu item type ', () => {
  ['default', 'blog'].forEach((layout) => {
    it(`can display a list of articles in the ${layout} layout in a menu item`, () => {
      cy.db_createArticle({ title: 'article 1' })
        .then(() => cy.db_createArticle({ title: 'article 2' }))
        .then(() => cy.db_createArticle({ title: 'article 3' }))
        .then(() => cy.db_createArticle({ title: 'article 4' }))
        .then(() => cy.db_createMenuItem({
          title: 'automated test',
          alias: 'automated-test',
          link: `index.php?option=com_content&view=category&id=2&layout=${layout}`,
          path: 'automated-test/root',
        }))
        .then(() => {
          cy.visit('/');
          cy.get('a:contains(automated test)').click();

          cy.contains('article 1');
          cy.contains('article 2');
          cy.contains('article 3');
          cy.contains('article 4');
        });
    });

    it(`can display a list of articles in the ${layout} layout without a menu item`, () => {
      cy.db_createArticle({ title: 'article 1' })
        .then(() => cy.db_createArticle({ title: 'article 2' }))
        .then(() => cy.db_createArticle({ title: 'article 3' }))
        .then(() => cy.db_createArticle({ title: 'article 4' }))
        .then(() => {
          cy.visit(`/index.php?option=com_content&view=category&id=2&layout=${layout}`);

          cy.contains('article 1');
          cy.contains('article 2');
          cy.contains('article 3');
          cy.contains('article 4');
        });
    });
  });

  it('can open the article form in the default layout', () => {
    cy.db_createArticle({ title: 'article 1' })
      .then(() => cy.db_createMenuItem({
        title: 'automated test',
        alias: 'automated-test',
        link: 'index.php?option=com_content&view=category&id=2&layout=default',
        path: 'automated-test/root',
      }))
      .then(() => {
        cy.doFrontendLogin();
        cy.visit('/');
        cy.get('a:contains(automated test)').click();
        cy.get('a:contains(New Article)').click();

        cy.get('#adminForm').should('exist');
      });
  });
});
