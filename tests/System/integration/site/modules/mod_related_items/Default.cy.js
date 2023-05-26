describe('Test in frontend that the related items module', () => {
  it('can display a list of related articles based on the metakey field', () => {
    cy.db_createArticle({ title: 'Main Article', metakey: 'joomla', featured: 1 })
      .then(() => cy.db_createArticle({ title: 'article with joomla keyword', metakey: 'joomla' }))
      .then(() => cy.db_createModule({ module: 'mod_related_items' }))
      .then(() => {
        cy.visit('/');
        cy.contains('a', 'Main Article').click();

        cy.contains('li', 'article with joomla keyword');
      });
  });
});
