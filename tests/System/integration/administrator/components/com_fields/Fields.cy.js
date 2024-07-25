describe('Test in backend that the custom fields list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_fields&view=fields&context=com_content.article&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Fields');
  });

  it('can display a list of fields', () => {
    cy.db_createField({ title: 'Test field' }).then(() => {
      cy.reload();

      cy.contains('Test field');
    });
  });

  it('can open the field form', () => {
    cy.clickToolbarButton('New');

    cy.contains('New Field');
  });

  it('can publish the test field', () => {
    cy.db_createField({ title: 'Test field', state: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test field');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Publish').click();

      cy.get('#system-message-container').contains('Field published').should('exist');
    });
  });

  it('can unpublish the test field', () => {
    cy.db_createField({ title: 'Test field', state: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test field');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();

      cy.get('#system-message-container').contains('Field unpublished').should('exist');
    });
  });

  it('can trash the test field', () => {
    cy.db_createField({ title: 'Test field' }).then(() => {
      cy.reload();
      cy.searchForItem('Test field');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();

      cy.get('#system-message-container').contains('Field trashed').should('exist');
    });
  });

  it('can delete the test field', () => {
    cy.db_createField({ title: 'Test field', state: -2 }).then(() => {
      cy.reload();
      cy.setFilter('state', 'Trashed');
      cy.searchForItem('Test field');
      cy.checkAllResults();
      cy.clickToolbarButton('empty trash');
      cy.clickDialogConfirm(true);

      cy.get('#system-message-container').contains('Field deleted').should('exist');
    });
  });
});
