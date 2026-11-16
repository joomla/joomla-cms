describe('Test accessibility on Media management', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
  });

  it('has no detectable a11y violations on Media initial view', () => {
    cy.visit('/administrator/index.php?option=com_media&path=local-images:/');
    cy.get('[aria-label="Change Ordering"]').should('be.visible').click();
    cy.runAccessibilityScan('#content');
  });

  it('has no detectable a11y violations on create new folder', () => {
    cy.visit('/administrator/index.php?option=com_media&path=local-images:/');
    cy.get('joomla-toolbar-button').contains('Create New Folder').should('be.visible').click();
    cy.get('.media-modal-backdrop .modal').should('be.visible').invoke('attr', 'style', 'animation: none');
    cy.runAccessibilityScan('.modal-content');
  });

  it('has no detectable a11y violations when Select all is checked on default view', () => {
    cy.visit('/administrator/index.php?option=com_media&path=local-images:/');
    cy.get('[aria-label="Toggle select all"]').should('be.visible').click();
    cy.runAccessibilityScan('#content');
  });

  it('has no detectable a11y violations when Select all is checked after view is changed', () => {
    cy.visit('/administrator/index.php?option=com_media&path=local-images:/');
    cy.get('[aria-label="Toggle between grid and table views"]').should('be.visible').click();
    cy.get('[aria-label="Toggle select all"]').should('be.visible').click();
    cy.runAccessibilityScan('#content');
  });

  it('has no detectable a11y violations when Info drawer is opened', () => {
    cy.visit('/administrator/index.php?option=com_media&path=local-images:/');
    cy.get('[aria-label="Toggle item info"]').should('be.visible').click();
    cy.get('.media-infobar').should('be.visible');
    cy.runAccessibilityScan('.media-infobar');
  });

  it('has no detectable a11y violations when Manage Item menu is opened', () => {
    cy.visit('/administrator/index.php?option=com_media&path=local-images:/');
    cy.get('body').then(($body) => {
      if (!$body.find('.media-browser-grid').length > 0) {
        cy.get('[aria-label="Toggle between grid and table views"]').should('be.visible').click();
      }
    });
    cy.get('[aria-label="Manage item: banners"]').click();
    cy.runAccessibilityScan('.media-browser-actions-list');
  });
});
