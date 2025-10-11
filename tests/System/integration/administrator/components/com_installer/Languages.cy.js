describe('Test in backend that the Installer', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_installer&view=languages');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Extensions: Languages');
  });

  it('has any Language installable', () => {
    cy.get('body').then((body) => {
      if (body.find('#installer-languages table').length === 0) {
        cy.get('#installer-languages .alert.alert-info').should('contain.text', 'No Matching Results');
        cy.checkForSystemMessage(`Can't connect to https://update.joomla.org/language/translationlist`);
      } else {
        cy.get('#installer-languages table').within(() => {
          cy.get('input[type="button"]').should('have.value', 'Install');
          cy.get('a[target="_blank"]').invoke('attr', 'href').should('match', /^https:\/\/update\.joomla\.org\/language\/details/);
        });
      }
    });
  });
});
