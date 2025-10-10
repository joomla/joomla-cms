describe('Test in backend that the checkin', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_checkin');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Maintenance: Global Check-in');
  });

  it('can display message', () => {
    cy.get('p.lead').should('contain.text', 'There are no tables with checked out items');
  });

  it('can display a list of checked out items', () => {
    cy.db_createArticle({ title: 'Test article', checked_out: '1', checked_out_time: '2024-01-01 20:00:00' }).then(() => {
      cy.visit('/administrator/index.php?option=com_checkin');
      cy.get('tr.row0').should('contain.text', 'content');
    });
  });

  it('can check in items', () => {
    cy.db_createArticle({ title: 'Test article', checked_out: '1', checked_out_time: '2024-01-01 20:00:00' }).then(() => {
      cy.visit('/administrator/index.php?option=com_checkin');
      cy.searchForItem('content');
      cy.checkAllResults();
      cy.get('#toolbar-checkin').click();
      cy.checkForSystemMessage('Item checked in');
    });
  });
});
