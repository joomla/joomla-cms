describe('Test in backend that the dialog', () => {
  beforeEach(() => cy.doAdministratorLogin());

  it('shows the header as plain text', () => {
    // A page which loads the script that creates dialogs from data-joomla-dialog attributes
    cy.visit('/administrator/index.php?option=com_installer&view=manage');

    cy.document().then((doc) => {
      const button = doc.createElement('button');
      button.type = 'button';
      button.id = 'header-text-dialog';
      button.dataset.joomlaDialog = JSON.stringify({
        popupType: 'inline',
        popupContent: 'Content',
        textHeader: 'Header <b id="header-marker">marker</b>',
      });
      doc.body.appendChild(button);
    });

    cy.get('#header-text-dialog').click();

    cy.get('joomla-dialog[type="inline"] .joomla-dialog-header h3').should('have.text', 'Header <b id="header-marker">marker</b>');
    cy.get('#header-marker').should('not.exist');
  });
});
