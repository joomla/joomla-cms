describe('Test in backend that the menu list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_menus&view=menus&filter=');
  });

  it('has a title', () => cy.get('h1.page-title').should('contain.text', 'Menus'));

  it('can display a list of menus', () => cy.contains('Main Menu'));

  it('can open the menu form', () => {
    cy.clickToolbarButton('New');

    cy.contains('Menus: Add');
  });
  it('can create a new site menu', () => {
    cy.clickToolbarButton('New');

    cy.get('input[name="jform[title]"]').type('Test Site Menu');
    cy.get('input[name="jform[menutype]"]').type('test-site-menu');
    cy.get('input[name="jform[description]"]').type('Test Site Menu Description');
    cy.clickToolbarButton('Save & Close');
    cy.get('.alert-message').should('contain.text', 'Menu saved');
  });
  it('can create a new administrator menu', () => {
    cy.get('#client_id').select('Administrator');

    cy.clickToolbarButton('New');

    cy.get('h1.page-title').should('contain.text', 'Menus: Add');
    cy.get('input[name="jform[title]"]').type('Test Admin Menu');
    cy.get('input[name="jform[menutype]"]').type('test-admin-menu');
    cy.get('input[name="jform[description]"]').type('Test Admin Menu Description');
    cy.clickToolbarButton('Save & Close');

    cy.get('.alert-message').should('contain.text', 'Menu saved');
  });
  it('can display the created administrator menu', () => {
    cy.get('#client_id').select('Administrator');

    cy.get('table#menuList')
      .contains('Test Admin Menu')
      .should('be.visible');
  });
    it('can create a module to display the created site menu', () => {
    cy.get('#client_id').select('Site');

    cy.get('table#menuList')
      .contains('Test Site Menu')
      .parents('tr')
      .find('button.btn.btn-sm.btn-primary') // Locate the button
      .should('contain.text', 'Add a module for this menu') // Validate the button text
      .click(); // Perform the click action

       cy.get('body').then(($body) => {
         cy.get('header').should('contain.text', 'Add a module for this menu');

         cy.get('input[name="jform[title]"]').type('Test Site Menu Module');
         cy.get('input[name="jform[position]"]').select('sidebar-right');

         cy.clickToolbarButton('Save & Close');
       });

      //there is no success message for this action so we check the menu item to see if it has a linked module
      cy.get('table#menuList')
        .contains('Test Site Menu')
        .parents('tr')
        .find('button.btn.btn-secondary.btn-sm.dropdown-toggle')
        .should('contain.text', 'Modules');
  });
  it('can create a module to display the created administrator menu', () => {
    cy.get('#client_id').select('Administrator');

    cy.get('table#menuList')
      .contains('Test Admin Menu')
      .parents('tr')
      .find('button.btn.btn-sm.btn-primary') // Locate the button
      .should('contain.text', 'Add a module for this menu') // Validate the button text
      .click(); // Perform the click action

       cy.get('body').then(($body) => {
         cy.get('header').should('contain.text', 'Add a module for this menu');

         cy.get('input[name="jform[title]"]').type('Test Admin Menu Module');
         cy.get('input[name="jform[position]"]').select('menu');

         cy.clickToolbarButton('Save & Close');
       });

      //there is no success message for this action so we check the menu item to see if it has a linked module
      cy.get('table#menuList')
        .contains('Test Admin Menu')
        .parents('tr')
        .find('button.btn.btn-secondary.btn-sm.dropdown-toggle')
        .should('contain.text', 'Modules');
  });

  it('can delete the created site menu', () => {
    cy.get('#client_id').select('Site');

    cy.get('table#menuList')
      .contains('Test Site Menu')
      .parents('tr')
      .find('input[type="checkbox"]')
      .check();
    cy.clickToolbarButton('Delete');

    cy.get('body').then(($body) => {
      if ($body.find('div.buttons-holder button[data-button-ok]').length > 0) {
        cy.get('div.buttons-holder button[data-button-ok]').click();
      }
    });

    cy.get('.alert-message').should('contain.text', 'Menu deleted');
  });
  it('can delete the created administrator menu', () => {
    cy.get('#client_id').select('Administrator');

    cy.get('table#menuList')
      .contains('Test Admin Menu')
      .parents('tr')
      .find('input[type="checkbox"]')
      .check();

    cy.clickToolbarButton('Delete');

    cy.get('body').then(($body) => {
      if ($body.find('div.buttons-holder button[data-button-ok]').length > 0) {
        cy.get('div.buttons-holder button[data-button-ok]').click();
      }
    });

    cy.get('.alert-message').should('contain.text', 'Menu deleted');
  });
});
