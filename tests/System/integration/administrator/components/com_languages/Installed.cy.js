describe('Test in backend that the installed languages', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_languages&view=installed');
  });

  it('have a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Languages');
  });

  it('have English Language', () => {
    cy.get('tr.row0').should('contain.text', 'English');
  });

  it('have a Language as default', () => {
    cy.get('span.icon-color-featured').should('exist');
  });

  it('have install languages link', () => {
    cy.contains(' Install Languages ');
  });
});
