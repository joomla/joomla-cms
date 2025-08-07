beforeEach(() => { cy.doAdministratorLogin(); });
afterEach(() => {
  cy.task('queryDB', "DELETE FROM #__banners WHERE name = 'Test banner'");
  cy.task('queryDB', "DELETE FROM #__contact_details WHERE name = 'Test contact'");
});

describe('Test that the shortcut system plugin', () => {
  it('can save (apply) edit form', () => {
    cy.visit('/administrator/index.php?option=com_banners&task=banner.add');
    cy.get('#jform_name').clear().type('Test banner').blur();
    cy.get('body').type('JA');

    cy.checkForSystemMessage('Banner saved.');
    cy.get('#jform_name').should('have.value', 'Test banner');
  });

  it('can save and close edit form', () => {
    cy.visit('/administrator/index.php?option=com_contact&task=contact.add');
    cy.get('#jform_name').clear().type('Test contact').blur();
    cy.get('body').type('JS');

    cy.checkForSystemMessage('Contact saved.');
    cy.get('#contactList').contains('Test contact');
  });

  it('can cancel edit form', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article').blur();
    cy.intercept('index.php?option=com_content&view=articles').as('listview');
    cy.get('body').type('JQ');

    cy.wait('@listview');
  });

  it('can open edit form', () => {
    cy.visit('/administrator/index.php?option=com_menus&view=menus');
    cy.get('body').type('JN');

    cy.get('h1.page-title').should('contain', 'Menus: Add');
  });

  it('can search in list view', () => {
    cy.visit('/administrator/index.php?option=com_plugins&view=plugins&filter=');
    cy.get('body').type('JF');

    cy.focused().should('have.attr', 'name', 'filter[search]').type('Keyboard Shortcuts{enter}');

    cy.get('#pluginList').contains('System - Keyboard Shortcuts');
  });

  it('can open component options', () => {
    cy.visit('/administrator/index.php?option=com_privacy');
    cy.get('body').type('JO');

    cy.get('h1.page-title').should('contain', 'Privacy: Options');
  });

  it('can open help screen', () => {
    cy.visit('/administrator/index.php?option=com_users&view=users');
    cy.window().then((win) => cy.stub(win, 'open').returns(win).as('help'));
    cy.get('body').type('JH');

    cy.get('@help').should('be.calledWithMatch', /https:\/\/help\.joomla\.org\/proxy\?keyref=Help\d+:Users&lang=en/);
  });

  it('can toggle menu', () => {
    cy.visit('/administrator/index.php');
    cy.get('#menu-collapse').contains('Toggle Menu').should('be.visible');
    cy.get('#wrapper').should('not.have.class', 'closed');
    cy.get('body').type('JM');

    cy.get('#menu-collapse').contains('Toggle Menu').should('not.be.visible');
    cy.get('#wrapper').should('have.class', 'closed');
    cy.get('body').type('JM');

    cy.get('#menu-collapse').contains('Toggle Menu').should('be.visible');
    cy.get('#wrapper').should('not.have.class', 'closed');
  });

  it('can open dashboard', () => {
    cy.visit('/administrator/index.php?option=com_cpanel&view=cpanel&dashboard=system');
    cy.intercept('index.php').as('dashboard');
    cy.get('body').type('JD');

    cy.wait('@dashboard');
    cy.get('h1.page-title').should('contain', 'Home Dashboard');
  });

  it('can open shortcut overview', () => {
    cy.visit('/administrator/index.php?option=com_cpanel&view=cpanel&dashboard=help');
    cy.get('div.container-fluid').contains('J then X Keyboard Shortcuts');
    cy.get('body').type('JX');

    cy.get('joomla-dialog[type="inline"] .joomla-dialog-header').should('contain', 'Keyboard Shortcuts');
  });
});
