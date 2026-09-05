describe('Test in backend that the contact form', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    // Clear the filter
    cy.visit('/administrator/index.php?option=com_contact&filter=');
  });
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__category_item_map WHERE context = 'com_contact.contact' AND item_id IN (SELECT id FROM #__contact_details WHERE name = 'Test contact')");
    cy.task('queryDB', "DELETE FROM #__contact_details WHERE name = 'Test contact'");
    cy.task('queryDB', "DELETE FROM #__categories WHERE title IN ('Test Contact Primary Category', 'Test Contact Additional Category')");
  });

  it('can create a contact', () => {
    cy.visit('/administrator/index.php?option=com_contact&task=contact.add');
    cy.get('#jform_name').clear().type('Test contact');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Contact saved.');
    cy.contains('Test contact');
  });

  it('shows additional categories field', () => {
    cy.visit('/administrator/index.php?option=com_contact&task=contact.add');

    cy.contains('label', 'Additional Categories').should('exist');
  });

  it('can assign additional categories to a contact', () => {
    cy.db_createCategory({
      title: 'Test Contact Primary Category',
      alias: 'test-contact-primary-category',
      path: 'test-contact-primary-category',
      extension: 'com_contact',
    }).then((primaryCategoryId) => cy.db_createCategory({
      title: 'Test Contact Additional Category',
      alias: 'test-contact-additional-category',
      path: 'test-contact-additional-category',
      extension: 'com_contact',
    }).then((additionalCategoryId) => {
      cy.db_createContact({ name: 'Test contact', catid: primaryCategoryId }).then((contact) => {
        cy.visit(`/administrator/index.php?option=com_contact&task=contact.edit&id=${contact.id}`);
        cy.get('#jform_secondary_categories').select(`${additionalCategoryId}`, { force: true });
        cy.clickToolbarButton('Save & Close');

        cy.checkForSystemMessage('Contact saved.');
        cy.task('queryDB', `SELECT category_id FROM #__category_item_map WHERE context = 'com_contact.contact' AND item_id = ${contact.id}`)
          .then((rows) => rows.map((row) => Number(row.category_id)))
          .should('include', additionalCategoryId);
      });
    }));
  });

  it('cannot delete an additional category assigned to a contact', () => {
    cy.db_createCategory({
      title: 'Test Contact Primary Category',
      alias: 'test-contact-primary-category',
      path: 'test-contact-primary-category',
      extension: 'com_contact',
    }).then((primaryCategoryId) => cy.db_createCategory({
      title: 'Test Contact Additional Category',
      alias: 'test-contact-additional-category',
      path: 'test-contact-additional-category',
      extension: 'com_contact',
    }).then((additionalCategoryId) => {
      cy.db_createContact({ name: 'Test contact', catid: primaryCategoryId }).then((contact) => {
        cy.task('queryDB', `INSERT INTO #__category_item_map (context, item_id, category_id, ordering) VALUES ('com_contact.contact', ${contact.id}, ${additionalCategoryId}, 0)`);
        cy.task('queryDB', `UPDATE #__categories SET published = -2 WHERE id = ${additionalCategoryId}`);
        cy.visit('/administrator/index.php?option=com_categories&view=categories&extension=com_contact&filter=');
        cy.setFilter('published', 'Trashed');
        cy.searchForItem('Test Contact Additional Category');
        cy.checkAllResults();
        cy.clickToolbarButton('empty trash');
        cy.clickDialogConfirm(true);

        cy.checkForSystemMessage('Delete not allowed for category Test Contact Additional Category. One item is assigned to this category.');
        cy.task('queryDB', `SELECT id FROM #__categories WHERE id = ${additionalCategoryId}`).should('have.length', 1);
      });
    }));
  });

  it('can change access level of a test contact', () => {
    cy.db_createContact({ name: 'Test contact' }).then((contact) => {
      cy.visit(`/administrator/index.php?option=com_contact&task=contact.edit&id=${contact.id}`);
      cy.get('#jform_access').select('Special');
      cy.clickToolbarButton('Save & Close');

      cy.get('td').contains('Special').should('exist');
    });
  });

  it('check redirection to list view', () => {
    cy.visit('/administrator/index.php?option=com_contact&task=contact.add');
    cy.intercept('index.php?option=com_contact&view=contacts').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
  });
});
