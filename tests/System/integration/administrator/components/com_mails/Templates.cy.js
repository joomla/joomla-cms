describe('Test in backend that the mails', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_mails&view=templates');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Mail Templates');
  });

  it('can display a list of mail templates', () => {
    cy.get('tr.row0').should('contain.text', 'User Actions Log');
  });

  it('check redirection to list view', () => {
    cy.visit('/administrator/index.php?option=com_mails&task=template.edit&template_id=com_actionlogs.notification&language=en-GB');
    cy.intercept('index.php?option=com_mails&view=templates').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
  });
});
