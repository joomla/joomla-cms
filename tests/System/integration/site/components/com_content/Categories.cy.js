describe('Test in frontend that the content categories view', () => {
  it('can display a list of categories without a menu item', () => {
    cy.db_createCategory({ title: 'automated test category 1', extension: 'com_content' })
      .then((id) => cy.db_createArticle({ title: 'automated test article 1', catid: id }))
      .then(() => cy.db_createCategory({ title: 'automated test category 2', extension: 'com_content' }))
      .then(async (id) => {
        await cy.db_createArticle({ title: 'automated test article 2', catid: id });
        await cy.db_createArticle({ title: 'automated test article 3', catid: id });
      })
      .then(() => {
        cy.visit('/index.php?option=com_content&view=categories');

        cy.contains('automated test category 1');
        cy.get(':nth-child(1) > .com-content-categories__item-title-wrapper > .com-content-categories__item-title > .badge').contains('Article Count: 1');
        cy.contains('automated test category 2');
        cy.get(':nth-child(2) > .com-content-categories__item-title-wrapper > .com-content-categories__item-title > .badge').contains('Article Count: 2');
      });
  });

  it('can display a list of categories in a menu item', () => {
    cy.db_createCategory({ title: 'automated test category 1', extension: 'com_content' })
      .then((id) => cy.db_createArticle({ title: 'automated test article 1', catid: id }))
      .then(() => cy.db_createCategory({ title: 'automated test category 2', extension: 'com_content' }))
      .then(async (id) => {
        await cy.db_createArticle({ title: 'automated test article 2', catid: id });
        await cy.db_createArticle({ title: 'automated test article 3', catid: id });
      })
      .then(() => cy.db_createMenuItem({ title: 'automated test categories', link: 'index.php?option=com_content&view=categories' }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test categories)').click();

        cy.contains('automated test category 1');
        cy.get(':nth-child(1) > .com-content-categories__item-title-wrapper > .com-content-categories__item-title > .badge').contains('Article Count: 1');
        cy.contains('automated test category 2');
        cy.get(':nth-child(2) > .com-content-categories__item-title-wrapper > .com-content-categories__item-title > .badge').contains('Article Count: 2');
      });
  });
});
