describe('Test in frontend that the content featured view', () => {
  it('can display an article', () => {
    cy.db_createArticle({ title: 'automated test article', featured: 1 }).then(() => {
      cy.visit('/');

      cy.contains('automated test article');
    });
  });

  it('can not display not featured articles', () => {
    cy.db_createArticle({ title: 'automated test article', featured: 0 }).then(() => {
      cy.visit('/');

      cy.contains('automated test article').should('not.exist');
    });
  });

  it('can navigate to the article', () => {
    cy.db_createArticle({ title: 'automated test article', featured: 1 }).then(() => {
      cy.visit('/');
      cy.get('.item-title a').click();

      cy.contains('h2', 'automated test article');
    });
  });

  it('can navigate to the category', () => {
    cy.db_createArticle({ title: 'automated test article', featured: 1 }).then(() => {
      cy.visit('/');
      cy.get('.category-name a').click();

      cy.contains('h1', 'Uncategorised');
    });
  });
});
