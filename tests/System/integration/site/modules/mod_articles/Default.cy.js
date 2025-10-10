describe('Test in frontend that the articles module', () => {
  it('can display the title of the article', () => {
    cy.db_createCategory({ extension: 'com_content' })
      .then(async (categoryId) => {
        await cy.db_createArticle({ title: 'automated test article', catid: categoryId });
        await cy.db_createModule({
          module: 'mod_articles',
          params: JSON.stringify({
            catid: categoryId,
            item_title: 1,
            show_introtext: 0,
          }),
        });
      })
      .then(() => {
        cy.visit('/');

        cy.contains('li', 'automated test article');
      });
  });
});
