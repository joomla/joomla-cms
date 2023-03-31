describe('Test the articles archive module', () => {
  it('can load in frontend and showing a list of months with archived articles', () => {
    cy.db_createArticle()
      .then(() => cy.db_createModule({ module: 'mod_articles_archive' }))
      .then(() => {
        cy.visit('/');

        cy.contains('li', 'January, 2023');
      });
  });
});
