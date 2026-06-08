describe('Test in backend that the menu list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
  });

  it('has a title', () => cy.get('h1.page-title').should('contain.text', 'Menus: Items'));

  it('can display a list of menu items', () => {
    cy.db_createMenuItem({ title: 'Test menu item' }).then(() => {
      cy.reload();

      cy.contains('Test menu item');
    });
  });

  it('can open the menu item form', () => {
    cy.clickToolbarButton('New');

    cy.contains('Menus: New Item');
  });

  it('can create a system link menu item', () => {
    cy.db_deleteMenuItem({ title: 'can create menu item' });

    cy.visit('administrator/index.php?option=com_menus&task=item.add');

    cy.contains('Menus: New Item');
    cy.get('#jform_title').clear().type('can create menu item');
    cy.get('button[data-modal-config*="Menu Item Type"]').first().click();

    cy.contains('Menu Item Type');
    cy.get('iframe').iframe().then(($body) => {
      cy.wrap($body).find('.accordion-button').contains('System Links').click();
      cy.wrap($body).find('a[data-type="url"]').click();
    });

    cy.get('input[name="jform[link]"]').click();
    cy.get('input[name="jform[link]"]').type('#');

    cy.clickToolbarButton('Save & Close');

    cy.get('#system-message-container').contains('Menu item saved.').should('exist');
  });

  it('can delete the test menu item', () => {
    cy.db_createMenuItem({ title: 'Test menu item' }).then(() => {
      cy.searchForItem('Test menu item');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();

      cy.checkForSystemMessage('Menu item trashed.');
    });
  });
});
