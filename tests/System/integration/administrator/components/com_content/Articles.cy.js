describe('Test in backend that the articles list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_content&view=articles&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Articles');
  });

  it('can display a list of articles', () => {
    cy.db_createArticle({ title: 'Test article' }).then(() => {
      cy.reload();

      cy.contains('Test article');
    });
  });

  it('can open the article form', () => {
    cy.clickToolbarButton('New');

    cy.contains('Articles: New');
  });

  it('can publish the test article', () => {
    cy.db_createArticle({ title: 'Test article', state: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Publish').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Article published.').should('exist');
    });
  });

  it('can unpublish the test article', () => {
    cy.db_createArticle({ title: 'Test article', state: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Article unpublished.').should('exist');
    });
  });

  it('can feature the test article', () => {
    cy.db_createArticle({ title: 'Test article', featured: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('.button-featured', 'Feature').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Article featured.').should('exist');
    });
  });

  it('can unfeature the test article', () => {
    cy.db_createArticle({ title: 'Test article', featured: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unfeature').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Article unfeatured.').should('exist');
    });
  });

  it('can trash the test article', () => {
    cy.db_createArticle({ title: 'Test article' }).then(() => {
      cy.reload();
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Article trashed.').should('exist');
    });
  });

  it('can delete the test article', () => {
    cy.db_createArticle({ title: 'Test article', state: -2 }).then(() => {
      cy.reload();
      cy.setFilter('published', 'Trashed');
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('empty trash');
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Article deleted.').should('exist');
    });
  });
});
