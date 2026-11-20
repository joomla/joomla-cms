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

      cy.checkForSystemMessage('Article published.');
    });
  });

  it('can unpublish the test article', () => {
    cy.db_createArticle({ title: 'Test article', state: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();

      cy.checkForSystemMessage('Article unpublished.');
    });
  });

  it('can feature the test article', () => {
    cy.db_createArticle({ title: 'Test article', featured: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('.button-featured', 'Feature').click();

      cy.checkForSystemMessage('Article featured.');
    });
  });

  it('can unfeature the test article', () => {
    cy.db_createArticle({ title: 'Test article', featured: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unfeature').click();

      cy.checkForSystemMessage('Article unfeatured.');
    });
  });

  it('can trash the test article', () => {
    cy.db_createArticle({ title: 'Test article' }).then(() => {
      cy.reload();
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();

      cy.checkForSystemMessage('Article trashed.');
    });
  });

  it('can delete the test article', () => {
    cy.db_createArticle({ title: 'Test article', state: -2 }).then(() => {
      cy.reload();
      cy.setFilter('published', 'Trashed');
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('empty trash');
      cy.clickDialogConfirm(true);

      cy.checkForSystemMessage('Article deleted.');
    });
  });

  it('can select articles with multiselect', () => {
    cy.db_createArticle({ title: 'Test article 1' })
      .then(() => cy.db_createArticle({ title: 'Test article 2' }))
      .then(() => cy.db_createArticle({ title: 'Test article 3' }))
      .then(() => cy.db_createArticle({ title: 'Test article 4' }))
      .then(() => cy.db_createArticle({ title: 'Test article 5' }))
      .then(() => {
        cy.reload();
        cy.searchForItem('Test article');
        cy.get('#cb2').click();
        cy.get('body').type('{shift}', { release: false });
        cy.get('#cb4').click();

        cy.clickToolbarButton('Action');
        cy.clickToolbarButton('Unpublish');

        cy.checkForSystemMessage('3 articles unpublished.');

        cy.get('thead input[name=\'checkall-toggle\']').should('not.be.checked');
        cy.get('#cb0').click();
        cy.get('body').type('{shift}', { release: false });
        cy.get('#cb4').click();
        cy.get('thead input[name=\'checkall-toggle\']').should('be.checked');

        cy.clickToolbarButton('Action');
        cy.clickToolbarButton('Unpublish');

        cy.checkForSystemMessage('2 articles unpublished.');

        cy.checkAllResults();
        cy.get('#cb2').click();
        cy.get('body').type('{shift}', { release: false });
        cy.get('#cb0').click();
        cy.get('body').type('{shift}');
        cy.get('#cb4').click();

        cy.clickToolbarButton('Action');
        cy.clickToolbarButton('Publish');

        cy.checkForSystemMessage('Article published.');
      });
  });
});
