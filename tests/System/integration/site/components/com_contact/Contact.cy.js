describe('Test in frontend that the contact details view', () => {
  afterEach(() => {
    cy.task('queryDB', 'DELETE FROM #__category_item_map WHERE context = \'com_contact.contact\' AND item_id IN (SELECT id FROM #__contact_details WHERE name IN (\'Test Contact Categories Info\', \'Test Contact Main Category Only\'))');
    cy.task('queryDB', 'DELETE FROM #__contact_details WHERE name IN (\'Test Contact Categories Info\', \'Test Contact Main Category Only\')');
    cy.task('queryDB', 'DELETE FROM #__categories WHERE title IN (\'Test Contact Primary Category\', \'Test Contact Additional Category\')');
    cy.task('queryDB', 'DELETE FROM #__menu WHERE title IN (\'Positive Contact Menu Item\', \'Negative Contact Menu Item\')');
  });

  it('can display a form', () => {
    cy.db_getUserId().then((id) => cy.db_createContact({ name: 'contact 1', user_id: id }))
      .then((contact) => {
        cy.visit(`/index.php?option=com_contact&view=contact&id=${contact.id}`);

        cy.contains('Contact Form');
        cy.get('.m-0').should('exist');
      });
  });

  it('can display a custom field', () => {
    cy.db_createFieldGroup({ title: 'automated test_field group', context: 'com_contact.mail' })
      .then((id) => cy.db_createField({
        group_id: id, context: 'com_contact.mail', type: 'checkboxes', fieldparams: JSON.stringify({ options: { options0: { name: 'test value', value: '' } } }),
      }))
      .then(() => cy.db_getUserId())
      .then((userId) => cy.db_createContact({ name: 'automated test contact 1', user_id: userId }))
      .then((contact) => {
        cy.visit(`/index.php?option=com_contact&view=contact&id=${contact.id}`);

        cy.contains('automated test_field group').should('exist');
        cy.contains('test field').should('exist');
        cy.contains('test value').should('exist');
      });
  });

  it('shows additional categories in the category info only when included via Menu Item', () => {
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
      cy.db_createContact({
        name: 'Test Contact Categories Info',
        alias: 'test-contact-categories-info',
        catid: primaryCategoryId,
      }).then((contact) => {
        cy.task('queryDB', `INSERT INTO #__category_item_map (context, item_id, category_id, ordering) VALUES ('com_contact.contact', ${contact.id}, ${additionalCategoryId}, 0)`);

        cy.db_createMenuItem({
          title: 'Positive Contact Menu Item',
          alias: 'positive-contact-menu-item',
          path: 'positive-contact-menu-item',
          link: `index.php?option=com_contact&view=contact&id=${contact.id}`,
          params: JSON.stringify({
            show_contact_category: 'show_no_link',
            include_secondary_categories: 1,
          }),
        }).then((positiveMenuId) => {
          cy.visit(`/index.php?option=com_contact&view=contact&id=${contact.id}&Itemid=${positiveMenuId}`);

          cy.get('.contact-category')
            .should('contain.text', 'Categories:')
            .and('contain.text', 'Test Contact Primary Category')
            .and('contain.text', 'Test Contact Additional Category');
        });

        cy.db_createMenuItem({
          title: 'Negative Contact Menu Item',
          alias: 'negative-contact-menu-item',
          path: 'negative-contact-menu-item',
          link: `index.php?option=com_contact&view=contact&id=${contact.id}`,
          params: JSON.stringify({
            show_contact_category: 'show_no_link',
            include_secondary_categories: 0,
          }),
        }).then((negativeMenuId) => {
          cy.visit(`/index.php?option=com_contact&view=contact&id=${contact.id}&Itemid=${negativeMenuId}`);

          cy.get('.contact-category')
            .should('contain.text', 'Category:')
            .and('contain.text', 'Test Contact Primary Category')
            .and('not.contain.text', 'Test Contact Additional Category');
        });
      });
    }));
  });
});
