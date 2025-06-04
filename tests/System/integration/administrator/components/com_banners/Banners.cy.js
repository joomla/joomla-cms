describe('Test in backend that the banners list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_banners&view=banners&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Banners');
  });

  it('can display a list of banners', () => {
    cy.db_createBanner({ name: 'Test banner' }).then(() => {
      cy.reload();

      cy.contains('Test banner');
    });
  });

  it('can open the banner form', () => {
    cy.clickToolbarButton('New');

    cy.contains('Banners: New');
  });

  it('can publish the test banner', () => {
    cy.db_createBanner({ name: 'Test banner', state: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test banner');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Publish').click();

      cy.checkForSystemMessage('Banner published.');
    });
  });

  it('can unpublish the test banner', () => {
    cy.db_createBanner({ name: 'Test banner', state: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test banner');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();

      cy.checkForSystemMessage('Banner unpublished.');
    });
  });

  it('can trash the test banner', () => {
    cy.db_createBanner({ name: 'Test banner' }).then(() => {
      cy.reload();
      cy.searchForItem('Test banner');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();

      cy.checkForSystemMessage('Banner trashed.');
    });
  });

  it('can delete the test banner', () => {
    cy.db_createBanner({ name: 'Test banner', state: -2 }).then(() => {
      cy.reload();
      cy.setFilter('published', 'Trashed');
      cy.searchForItem('Test banner');
      cy.checkAllResults();
      cy.clickToolbarButton('empty trash');
      cy.clickDialogConfirm(true);

      cy.checkForSystemMessage('Banner deleted.');
    });
  });
});
