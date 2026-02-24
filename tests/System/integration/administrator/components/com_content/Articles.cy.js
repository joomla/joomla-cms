describe('Test in backend that the articles list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_content&view=articles&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Articles');
  });

  it('exposes async backend option disabled by default', () => {
    cy.window().then((win) => {
      const option = win.Joomla.getOptions('com_content.async_admin', {});

      expect(option).to.have.property('enabled', false);
    });
  });

  it('falls back when async backend feature is disabled', () => {
    cy.window().then((win) => {
      let fallbackCalled = false;

      return win.Joomla.asyncAdminRequest({
        url: 'index.php?option=com_content&view=articles',
        featureFlagKey: 'com_content.async_admin',
        onFallback: () => {
          fallbackCalled = true;
        },
      }).then((result) => {
        expect(result).to.have.property('mode', 'fallback');
        expect(fallbackCalled).to.equal(true);
      });
    });
  });

  it('uses async helper for publish task when async backend is enabled', () => {
    cy.window().then((win) => {
      const originalAsyncRequest = win.Joomla.asyncAdminRequest;
      let asyncCalled = false;

      win.Joomla.loadOptions({ 'com_content.async_admin': { enabled: true } });
      win.Joomla.asyncAdminRequest = (options) => {
        asyncCalled = true;
        expect(options).to.have.property('fallbackTask', 'articles.publish');

        return Promise.resolve({ mode: 'fallback' });
      };

      win.Joomla.submitbutton('articles.publish', 'adminForm', false);

      expect(asyncCalled).to.equal(true);
      win.Joomla.asyncAdminRequest = originalAsyncRequest;
    });
  });

  it('uses async helper for transition task when async backend is enabled', () => {
    cy.window().then((win) => {
      const originalAsyncRequest = win.Joomla.asyncAdminRequest;
      let asyncCalled = false;

      win.Joomla.loadOptions({ 'com_content.async_admin': { enabled: true } });
      win.Joomla.asyncAdminRequest = (options) => {
        asyncCalled = true;
        expect(options).to.have.property('fallbackTask', 'articles.runTransition');

        return Promise.resolve({ mode: 'fallback' });
      };

      win.Joomla.submitbutton('articles.runTransition', 'adminForm', false);

      expect(asyncCalled).to.equal(true);
      win.Joomla.asyncAdminRequest = originalAsyncRequest;
    });
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
