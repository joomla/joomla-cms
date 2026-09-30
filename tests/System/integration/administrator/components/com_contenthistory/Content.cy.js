describe('Test in backend that the content history list', () => {
  beforeEach(() => {
    cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Test article versions'");
    cy.doAdministratorLogin();
  });

  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Test article versions'");
  });

  it('has a title', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');
    cy.get('.joomla-dialog-header').should('contain.text', 'Versions');
  });

  it('can display a list of content history', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');

    const currentDate = new Date();
    const formattedDate = `${currentDate.getFullYear()}-${(currentDate.getMonth() + 1).toString().padStart(2, '0')}-${currentDate.getDate().toString().padStart(2, '0')}`;
    cy.get('joomla-dialog[type="iframe"]').as('dialogContent');
    cy.get('@dialogContent').should('be.visible');
    cy.get('@dialogContent').within(() => {
      cy.get('header.joomla-dialog-header').should('contain', 'Versions');
      cy.get('section.joomla-dialog-body iframe').iframe().within(() => {
        cy.get('a').should('contain.text', formattedDate);
      });
      cy.get('header.joomla-dialog-header button.button-close.btn-close').click();
    });
    cy.get('@dialogContent').should('not.exist');
  });

  it('can open the history content item modal', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');

    cy.get('joomla-dialog[type="iframe"]').as('dialogContent');
    cy.get('@dialogContent').should('be.visible');
    cy.get('@dialogContent').within(() => {
      cy.get('header.joomla-dialog-header').should('contain', 'Versions');
      cy.get('section.joomla-dialog-body iframe').iframe().within(() => {
        cy.location('href').then((location) => {
          cy.get('a').invoke('attr', 'data-url').then((url) => {
            cy.visit(new URL(url, location).href);
          });
        });
      });
    });

    cy.get('h1').should('contain.text', 'Preview of version');
    cy.contains('Test article versions').should('be.visible');
  });

  it('cannot compare one history content item only', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');

    cy.get('joomla-dialog[type="iframe"]').as('dialogContent');
    cy.get('@dialogContent').should('be.visible');
    cy.get('@dialogContent').within(() => {
      cy.get('header.joomla-dialog-header').should('contain', 'Versions');
      cy.get('section.joomla-dialog-body iframe').iframe().within(() => {
        cy.checkAllResults();
        cy.get('button.button-compare').should('contain.text', 'Compare').click();
        cy.checkForSystemMessage('Please select two versions.');
      });
    });
  });

  it('can delete a history content item', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');

    cy.get('joomla-dialog[type="iframe"]').as('dialogContent');
    cy.get('@dialogContent').should('be.visible');
    cy.get('@dialogContent').within(() => {
      cy.get('header.joomla-dialog-header').should('contain', 'Versions');
      cy.get('section.joomla-dialog-body iframe').iframe().within(() => {
        cy.checkAllResults();
        cy.get('button.button-delete').should('contain.text', 'Delete').click();
      });
      cy.get('section.joomla-dialog-body iframe').then((iframe) => {
        return new Cypress.Promise((resolve) => {
          iframe.on('load', () => resolve());
        });
      });
      cy.get('section.joomla-dialog-body iframe').iframe().within(() => {
        cy.checkForSystemMessage('History version deleted.');
      });
    });
  });

  it('can keep on a history content item', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');

    cy.get('joomla-dialog[type="iframe"]').as('dialogContent');
    cy.get('@dialogContent').should('be.visible');
    cy.get('@dialogContent').within(() => {
      cy.get('header.joomla-dialog-header').should('contain', 'Versions');
      cy.get('section.joomla-dialog-body iframe').iframe().within(() => {
        cy.checkAllResults();
        cy.get('button.button-keep').should('contain.text', 'Keep On/Off').click();
      });
      cy.get('section.joomla-dialog-body iframe').then((iframe) => {
        return new Cypress.Promise((resolve) => {
          iframe.on('load', () => resolve());
        });
      });
      cy.get('section.joomla-dialog-body iframe').iframe().within(() => {
        cy.checkForSystemMessage('Changed the keep forever value for a history version.');
      });
    });
  });

  it('can restore a history content item', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');

    cy.get('joomla-dialog[type="iframe"]').as('dialogContent');
    cy.get('@dialogContent').should('be.visible');
    cy.get('@dialogContent').within(() => {
      cy.get('header.joomla-dialog-header').should('contain', 'Versions');
      cy.get('section.joomla-dialog-body iframe').iframe().within(() => {
        cy.checkAllResults();
        cy.get('button.button-load').should('contain.text', 'Restore').click();
      });
      cy.get('section.joomla-dialog-body iframe').then((iframe) => {
        return new Cypress.Promise((resolve) => {
          iframe.on('load', () => resolve());
        });
      });
      cy.get('section.joomla-dialog-body iframe').iframe().within(() => {
        cy.checkForSystemMessage('Prior version restored.');
      });
    });
  });
});
