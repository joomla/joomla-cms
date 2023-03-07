describe('Test that the blog view ', () => {
  it('can display a list of articles in a menu item', function () {
    cy.db_createArticle({title: 'article 1'})
        .then(() => cy.db_createArticle({title: 'article 2'}))
        .then(() => cy.db_createArticle({title: 'article 3'}))
        .then(() => cy.db_createArticle({title: 'article 4'}))
        .then(() => cy.db_createMenuItem({'title': 'automated test', link: 'index.php?option=com_content&view=category&layout=blog&id=2'}))
        .then(() => {
            cy.visit('/');
            cy.get('a:contains(automated test)').click();

            cy.contains('article 1');
            cy.contains('article 2');
            cy.contains('article 3');
            cy.contains('article 4');
        });
  });

  it('can display a list of articles without a menu item', function () {
    cy.db_createArticle({title: 'article 1'})
        .then(() => cy.db_createArticle({title: 'article 2'}))
        .then(() => cy.db_createArticle({title: 'article 3'}))
        .then(() => cy.db_createArticle({title: 'article 4'}))
        .then(() => {
            cy.visit('/index.php?option=com_content&view=category&layout=blog&id=2');

            cy.contains('article 1');
            cy.contains('article 2');
            cy.contains('article 3');
            cy.contains('article 4');
        });
  });
});
