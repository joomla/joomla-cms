describe('Test in backend that the clients form', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    // Clear the filter
    cy.visit('/administrator/index.php?option=com_banners&view=clients&filter=');
  });
  afterEach(() => cy.task('queryDB', "DELETE FROM #__banner_clients WHERE name = 'test banner Client'"));

  it('can create a client', () => {
    cy.visit('/administrator/index.php?option=com_banners&task=client.add');
    cy.get('#jform_name').clear().type('test banner Client');
    cy.get('#jform_contact').clear().type('test banner Client');
    cy.clickToolbarButton('Save & Close');

    cy.get('#system-message-container').contains('Client saved.').should('exist');
    cy.contains('test banner Client');
  });

  it('check redirection to list view', () => {
    cy.visit('/administrator/index.php?option=com_banners&task=client.add');
    cy.intercept('index.php?option=com_banners&view=clients').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
  });

  it('can edit a client', () => {
    cy.db_createBannerClient({ name: 'test banner Client' }).then((banner) => {
      cy.visit(`/administrator/index.php?option=com_banners&task=banner.edit&id=${banner.id}`);
      cy.get('#jform_name').clear().type('Test banner edited');
      cy.clickToolbarButton('Save & Close');

      cy.contains('Test banner edited');
    });
  });
});
