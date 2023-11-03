describe('Test in frontend that the content category view', () => {
  ['default', 'blog'].forEach((layout) => {
    it(`can display a list of articles in the ${layout} layout in a menu item`, () => {
      cy.db_createArticle({ title: 'article 1' })
        .then((article) => cy.db_createArticle({ title: 'article 2', catid: article.catid }))
        .then((article) => cy.db_createArticle({ title: 'article 3', catid: article.catid }))
        .then((article) => cy.db_createArticle({ title: 'article 4', catid: article.catid }))
        .then((article) => cy.db_createMenuItem({
          title: 'automated test',
          alias: 'automated-test',
          link: `index.php?option=com_content&view=category&id=${article.catid}&layout=${layout}`,
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
        .then((article) => cy.db_createArticle({ title: 'article 2', catid: article.catid }))
        .then((article) => cy.db_createArticle({ title: 'article 3', catid: article.catid }))
        .then((article) => cy.db_createArticle({ title: 'article 4', catid: article.catid }))
        .then((article) => {
          cy.visit(`/index.php?option=com_content&view=category&id=${article.catid}&layout=${layout}`);

          cy.contains('article 1');
          cy.contains('article 2');
          cy.contains('article 3');
          cy.contains('article 4');
        });
    });
  });

  it('can open the article form in the default layout', () => {
    cy.db_createArticle({ title: 'article 1' })
      .then((article) => cy.db_createMenuItem({
        title: 'automated test',
        alias: 'automated-test',
        link: `index.php?option=com_content&view=category&id=${article.catid}&layout=default`,
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
