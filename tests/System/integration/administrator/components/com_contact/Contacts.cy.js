describe('Test in backend that the contacts list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_contact&view=contacts&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Contacts');
  });

  it('can display a list of contacts', () => {
    cy.db_createContact({ name: 'Test contact' }).then(() => {
      cy.reload();

      cy.contains('Test contact');
    });
  });

  it('can open the contact form', () => {
    cy.clickToolbarButton('New');

    cy.contains('Contacts: New');
  });

  it('can publish the test contact', () => {
    cy.db_createContact({ name: 'Test contact', published: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test contact');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Publish').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Contact published.').should('exist');
    });
  });

  it('can unpublish the test contact', () => {
    cy.db_createContact({ name: 'Test contact', published: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test contact');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Contact unpublished.').should('exist');
    });
  });

  it('can feature the test contact', () => {
    cy.db_createContact({ name: 'Test contact', featured: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test contact');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('.button-featured', 'Feature').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Contact featured.').should('exist');
    });
  });

  it('can unfeature the test contact', () => {
    cy.db_createContact({ name: 'Test contact', featured: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test contact');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unfeature').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Contact unfeatured.').should('exist');
    });
  });

  it('can trash the test contact', () => {
    cy.db_createContact({ name: 'Test contact' }).then(() => {
      cy.reload();
      cy.searchForItem('Test contact');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Contact trashed.').should('exist');
    });
  });

  it('can delete the test contact', () => {
    cy.db_createContact({ name: 'Test contact', published: -2 }).then(() => {
      cy.reload();
      cy.setFilter('published', 'Trashed');
      cy.searchForItem('Test contact');
      cy.checkAllResults();
      cy.clickToolbarButton('empty trash');
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Contact deleted.').should('exist');
    });
  });
});
