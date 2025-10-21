describe('Test in backend that the banner clients form', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    // Clear the filter
    cy.visit('/administrator/index.php?option=com_banners&view=clients&filter=');
  });
  afterEach(() => cy.task('queryDB', "DELETE FROM #__banner_clients WHERE name = 'test banner client'"));

  it('can create a client', () => {
    cy.visit('/administrator/index.php?option=com_banners&task=client.add');
    cy.get('#jform_name').clear().type('test banner client');
    cy.get('#jform_contact').clear().type('test banner client');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Client saved.');
    cy.contains('test banner client');
  });

  it('check redirection to list view', () => {
    cy.visit('/administrator/index.php?option=com_banners&task=client.add');
    cy.intercept('index.php?option=com_banners&view=clients').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
  });

  it('can edit a client', () => {
    cy.db_createBannerClient({ name: 'test client' }).then((bannerClient) => {
      cy.visit(`/administrator/index.php?option=com_banners&task=client.edit&id=${bannerClient.id}`);
      cy.get('#jform_name').clear().type('test banner client');
      cy.get('#jform_contact').clear().type('test banner');
      cy.clickToolbarButton('Save & Close');

      cy.contains('test banner client');
    });
  });
});
