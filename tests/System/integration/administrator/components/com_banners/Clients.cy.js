describe('Test in backend that the clients list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_banners&view=clients&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Banners: Clients');
  });

  it('can display a list of clients', () => {
    cy.db_createBannerClient({ name: 'test banner client' }).then(() => {
      cy.reload();

      cy.contains('test banner client');
    });
  });

  it('can open the clients form', () => {
    cy.clickToolbarButton('New');

    cy.contains('Banners: New Client');
  });

  it('can publish the test client', () => {
    cy.db_createBannerClient({ name: 'test banner client', state: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('test banner client');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Publish').click();

      cy.checkForSystemMessage('Client published.');
    });
  });

  it('can unpublish the test client', () => {
    cy.db_createBannerClient({ name: 'test banner client', state: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('test banner client');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();

      cy.checkForSystemMessage('Client unpublished.');
    });
  });

  it('can trash the test client', () => {
    cy.db_createBannerClient({ name: 'test banner client' }).then(() => {
      cy.reload();
      cy.searchForItem('test banner client');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();

      cy.checkForSystemMessage('Client trashed.');
    });
  });

  it('can delete the test client', () => {
    cy.db_createBannerClient({ name: 'test banner client', state: -2 }).then(() => {
      cy.reload();
      cy.setFilter('state', 'Trashed');
      cy.searchForItem('test banner client');
      cy.checkAllResults();
      cy.clickToolbarButton('empty trash');
      cy.clickDialogConfirm(true);

      cy.checkForSystemMessage('Client deleted.');
    });
  });
});
