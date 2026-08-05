describe('Test in backend that the banners form', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    // Clear the filter
    cy.visit('/administrator/index.php?option=com_banners&filter=');
  });
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__category_item_map WHERE context = 'com_banners.banner' AND item_id IN (SELECT id FROM #__banners WHERE name = 'Test banner')");
    cy.task('queryDB', "DELETE FROM #__banners WHERE name = 'Test banner'");
    cy.task('queryDB', "DELETE FROM #__categories WHERE title IN ('Test Banner Primary Category', 'Test Banner Additional Category')");
  });

  it('can create a banner', () => {
    cy.visit('/administrator/index.php?option=com_banners&task=banner.add');
    cy.get('#jform_name').clear().type('Test banner');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Banner saved.');
    cy.contains('Test banner');
  });

  it('shows additional categories field', () => {
    cy.visit('/administrator/index.php?option=com_banners&task=banner.add');

    cy.contains('label', 'Additional Categories').should('exist');
  });

  it('can assign additional categories to a banner', () => {
    cy.db_createCategory({
      title: 'Test Banner Primary Category',
      alias: 'test-banner-primary-category',
      path: 'test-banner-primary-category',
      extension: 'com_banners',
    }).then((primaryCategoryId) => cy.db_createCategory({
      title: 'Test Banner Additional Category',
      alias: 'test-banner-additional-category',
      path: 'test-banner-additional-category',
      extension: 'com_banners',
    }).then((additionalCategoryId) => {
      cy.db_createBanner({ name: 'Test banner', catid: primaryCategoryId }).then((banner) => {
        cy.visit(`/administrator/index.php?option=com_banners&task=banner.edit&id=${banner.id}`);
        cy.get('#jform_secondary_categories').select(`${additionalCategoryId}`, { force: true });
        cy.clickToolbarButton('Save & Close');

        cy.checkForSystemMessage('Banner saved.');
        cy.task('queryDB', `SELECT category_id FROM #__category_item_map WHERE context = 'com_banners.banner' AND item_id = ${banner.id}`)
          .then((rows) => rows.map((row) => Number(row.category_id)))
          .should('include', additionalCategoryId);
      });
    }));
  });

  it('check redirection to list view', () => {
    cy.visit('/administrator/index.php?option=com_banners&task=banner.add');
    cy.intercept('index.php?option=com_banners&view=banners').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
  });

  it('can edit a banner', () => {
    cy.db_createBanner({ name: 'Test Banner' }).then((banner) => {
      cy.visit(`/administrator/index.php?option=com_banners&task=banner.edit&id=${banner.id}`);
      cy.get('#jform_name').clear().type('Test banner edited');
      cy.clickToolbarButton('Save & Close');

      cy.contains('Test banner edited');
    });
  });
});
