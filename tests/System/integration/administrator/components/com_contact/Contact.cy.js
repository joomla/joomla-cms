describe('Test in backend that the contact form', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    // Clear the filter
    cy.visit('/administrator/index.php?option=com_contact&filter=');
  });
  afterEach(() => cy.task('queryDB', "DELETE FROM #__contact_details WHERE name = 'Test contact'"));

  it('can create a contact', () => {
    cy.visit('/administrator/index.php?option=com_contact&task=contact.add');
    cy.get('#jform_name').clear().type('Test contact');
    cy.clickToolbarButton('Save & Close');

    cy.get('#system-message-container').contains('Contact saved.').should('exist');
    cy.contains('Test contact');
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
