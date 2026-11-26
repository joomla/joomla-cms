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

  describe('custom-field filter', () => {
    let fieldId;

    beforeEach(() => {
      fieldId = undefined;
    });

    afterEach(() => {
      if (fieldId) {
        return cy.task('queryDB', `DELETE FROM #__fields_values WHERE field_id = ${fieldId}`);
      }

      return undefined;
    });

    it('filters, remembers, and clears an opted-in contact field', () => {
      cy.db_createField({
        title: 'Contacts list filter',
        name: 'contact-list-filter',
        label: 'Contacts list filter',
        context: 'com_contact.contact',
        type: 'list',
        required: 0,
        params: JSON.stringify({ show_in_admin_list_filter: 1 }),
        fieldparams: JSON.stringify({ options: { options0: { name: 'Matched', value: 'matched' } } }),
      }).then((id) => {
        fieldId = id;
        return cy.db_createContact({ name: 'Contact filter matched' });
      }).then((item) => cy.db_createFieldValue({ field_id: fieldId, item_id: item.id, value: 'matched' }))
        .then(() => cy.db_createContact({ name: 'Contact filter unmatched' }))
        .then(() => {
          cy.reload();
          cy.get(`#filter_customfield_${fieldId}`).should('not.be.disabled')
            .select('matched', { force: true });
          cy.get('#contactList').should('contain.text', 'Contact filter matched')
            .and('not.contain.text', 'Contact filter unmatched');

          cy.reload();
          cy.get(`#filter_customfield_${fieldId} option:selected`).should('have.value', 'matched');
          cy.get('#contactList').should('contain.text', 'Contact filter matched')
            .and('not.contain.text', 'Contact filter unmatched');

          cy.get('.js-stools-btn-clear').click();
          cy.get('#contactList').should('contain.text', 'Contact filter matched')
            .and('contain.text', 'Contact filter unmatched');
        });
    });
  });
});
