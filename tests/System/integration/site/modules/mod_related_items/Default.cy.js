describe('Test the related items module', () => {
  it('can load in frontend showing a list of related articles based on the metakey field', () => {
    cy.db_createArticle({title: 'Main Article', metakey: 'joomla' })
      .then(() => cy.db_createArticle({title: 'article with joomla keyword', metakey: 'joomla' }))
      .then(() => cy.db_createMenuItem({ title: 'Single article', alias: 'single-article', link: 'index.php?option=com_content&view=article&id=1', path: 'single-article'}))
      .then(() => cy.db_createModule({ module: 'mod_related_items' }))
      .then(() => {
        cy.visit('/index.php/single-article');

        cy.contains('li', 'article with joomla keyword');
      });
  });
});
