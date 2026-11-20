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

      cy.checkForSystemMessage('Contact published.');
    });
  });

  it('can unpublish the test contact', () => {
    cy.db_createContact({ name: 'Test contact', published: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test contact');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();

      cy.checkForSystemMessage('Contact unpublished.');
    });
  });

  it('can feature the test contact', () => {
    cy.db_createContact({ name: 'Test contact', featured: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test contact');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('.button-featured', 'Feature').click();

      cy.checkForSystemMessage('Contact featured.');
    });
  });

  it('can unfeature the test contact', () => {
    cy.db_createContact({ name: 'Test contact', featured: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test contact');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unfeature').click();

      cy.checkForSystemMessage('Contact unfeatured.');
    });
  });

  it('can trash the test contact', () => {
    cy.db_createContact({ name: 'Test contact' }).then(() => {
      cy.reload();
      cy.searchForItem('Test contact');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();

      cy.checkForSystemMessage('Contact trashed.');
    });
  });

  it('can delete the test contact', () => {
    cy.db_createContact({ name: 'Test contact', published: -2 }).then(() => {
      cy.reload();
      cy.setFilter('published', 'Trashed');
      cy.searchForItem('Test contact');
      cy.checkAllResults();
      cy.clickToolbarButton('empty trash');
      cy.clickDialogConfirm(true);

      cy.checkForSystemMessage('Contact deleted.');
    });
  });

  it('can select contacts with multiselect', () => {
    cy.db_createContact({ name: 'Test contact 1' })
      .then(() => cy.db_createContact({ name: 'Test contact 2' }))
      .then(() => cy.db_createContact({ name: 'Test contact 3' }))
      .then(() => cy.db_createContact({ name: 'Test contact 4' }))
      .then(() => cy.db_createContact({ name: 'Test contact 5' }))
      .then(() => {
        cy.reload();
        cy.searchForItem('Test contact');
        cy.get('#cb1').click();
        cy.get('body').type('{shift}', { release: false });
        cy.get('#cb3').click();

        cy.clickToolbarButton('Action');
        cy.clickToolbarButton('Unpublish');

        cy.checkForSystemMessage('3 contacts unpublished.');

        cy.get('thead input[name=\'checkall-toggle\']').should('not.be.checked');
        cy.get('#cb0').click();
        cy.get('body').type('{shift}', { release: false });
        cy.get('#cb4').click();
        cy.get('thead input[name=\'checkall-toggle\']').should('be.checked');

        cy.clickToolbarButton('Action');
        cy.clickToolbarButton('Unpublish');

        cy.checkForSystemMessage('2 contacts unpublished.');

        cy.checkAllResults();
        cy.get('#cb2').click();
        cy.get('body').type('{shift}', { release: false });
        cy.get('#cb4').click();
        cy.get('body').type('{shift}');
        cy.get('#cb0').click();

        cy.clickToolbarButton('Action');
        cy.clickToolbarButton('Publish');

        cy.checkForSystemMessage('Contact published.');
      });
  });
});
