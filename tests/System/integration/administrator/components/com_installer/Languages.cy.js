describe('Test in backend that the Installer', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_installer&view=languages');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Extensions: Languages');
  });

  it('has Afrikaans Language installable', () => {
    cy.get('body').then((body) => {
      if (body.find('#installer-languages table').length === 0) {
        cy.get('#installer-languages .alert.alert-info').should('contain.text', 'No Matching Results');
        cy.checkForSystemMessage(`Can't connect to https://update.joomla.org/language/translationlist`);
      } else {
        cy.get('tr.row0').should('contain.text', 'Afrikaans').then(() => {
          cy.get('input.btn.btn-primary.btn-sm').should('exist');
        });
      }
    });
  });
});
