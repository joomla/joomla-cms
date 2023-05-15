describe('Test in frontend that the content archived view', () => {
  it('can display archived articles in a menu item', () => {
    cy.db_createMenuItem({ title: 'automated test archive article', link: 'index.php?option=com_content&view=archive' })
      .then(() => cy.db_createArticle({ title: 'article 1', state: 2 }))
      .then(() => cy.db_createArticle({ title: 'article 2', state: 2 }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test archive article)').click();

        cy.contains('article 1');
        cy.contains('article 2');
      });
  });

  it('can display archived articles without a menu item', () => {
    cy.db_createArticle({ title: 'article 1', state: 2 })
      .then(() => cy.db_createArticle({ title: 'article 2', state: 2 }))
      .then(() => {
        cy.visit('/index.php?option=com_content&view=archive');

        cy.contains('article 1');
        cy.contains('article 2');
      });
  });

  it('can not display not archived articles', () => {
    cy.db_createMenuItem({ title: 'automated test archive article', link: 'index.php?option=com_content&view=archive' })
      .then(() => cy.db_createArticle({ title: 'article 1', state: 2 }))
      .then(() => cy.db_createArticle({ title: 'article 2', state: 1 }))
      .then(() => cy.db_createArticle({ title: 'article 3', state: 0 }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test archive article)').click();

        cy.contains('article 1');
        cy.contains('article 2').should('not.exist');
        cy.contains('article 3').should('not.exist');
      });
  });
});
