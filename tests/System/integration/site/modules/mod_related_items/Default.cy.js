describe('Test in frontend that the related items module', () => {
  it('can display a list of related articles based on the metakey field', () => {
    cy.db_createArticle({ title: 'Main Article', metakey: 'joomla', featured: 1, created: '2022-09-09 20:00:00', modified: '2022-09-09 20:00:00'})
      .then(() => cy.db_createArticle({ title: 'article with joomla keyword', metakey: 'joomla', created: '2022-09-09 20:00:00', modified: '2022-09-09 20:00:00' }))
      .then(() => cy.db_createModule({ module: 'mod_related_items' }))
      .then(() => {
        cy.visit('/');
        cy.contains('a', 'Main Article').click();

        cy.contains('li', 'article with joomla keyword');
      });
  });
});
