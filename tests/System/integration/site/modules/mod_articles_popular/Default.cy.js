describe('Test in frontend that the popular articles module', () => {
  it('can display the title of the test article', () => {
    cy.db_createCategory({ extension: 'com_content' })
      .then(async (categoryId) => {
        await cy.db_createArticle({ title: 'automated test article', catid: categoryId });
        await cy.db_createModule({ module: 'mod_articles_popular', params: JSON.stringify({ catid: categoryId }) });
      })
      .then(() => {
        cy.visit('/');

        cy.contains('li', 'automated test article');
      });
  });
});
