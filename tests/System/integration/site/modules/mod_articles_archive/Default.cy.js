describe('Test in frontend that the articles archive module', () => {
  it('can display a list of months with archived articles', () => {
    cy.db_createArticle({ state: 2, created: '2022-09-09 20:00:00', modified: '2022-09-09 20:00:00' })
      .then(() => cy.db_createModule({ module: 'mod_articles_archive' }))
      .then(() => {
        cy.visit('/');

        cy.contains('li', 'September, 2022');
      });
  });
});
