describe('Test in backend that the media manager', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.intercept('*format=json*task=api.files*').as('getMedia');
  });

  it('has a title', () => {
    cy.visit('/administrator/index.php?option=com_media');
    cy.wait('@getMedia');

    cy.get('h1.page-title').should('contain.text', 'Media');
  });

  it('can display the local media data when no path is defined', () => {
    cy.visit('/administrator/index.php?option=com_media');
    cy.wait('@getMedia');

    cy.get('.media-container').should('contain.text', 'Local');
    cy.get('.media-browser .media-browser-item-directory').should('exist');
    cy.get('.media-browser .media-browser-image').should('exist');
  });

  it('can display the local media data when an adapter is defined in the url', () => {
    cy.visit('/administrator/index.php?option=com_media&path=local-images:/');
    cy.wait('@getMedia');

    cy.get('.media-browser .media-browser-image').should('contain.text', 'joomla_black.png');
  });

  it('can display the local media data when an adapter is defined in the session', () => {
    window.sessionStorage.setItem('joomla.mediamanager', JSON.stringify({ selectedDirectory: 'local-images:/' }));
    cy.visit('/administrator/index.php?option=com_media');
    cy.wait('@getMedia');

    cy.get('.media-browser .media-browser-image').should('contain.text', 'joomla_black.png');
  });

  it('can display the first adapter files when an invalid adapter is defined in the url', () => {
    cy.visit('/administrator/index.php?option=com_media&path=local-invalid:/');
    cy.wait('@getMedia');

    cy.get('.media-browser .media-browser-image').should('contain.text', 'joomla_black.png');
  });

  it('can display the first adapter files when an invalid adapter is defined in the session', () => {
    window.sessionStorage.setItem('joomla.mediamanager', JSON.stringify({ selectedDirectory: 'local-invalid:/' }));
    cy.visit('/administrator/index.php?option=com_media');
    cy.wait('@getMedia');

    cy.get('.media-browser .media-browser-image').should('contain.text', 'joomla_black.png');
  });

  it('can display the local media data from the path in the url', () => {
    cy.visit('/administrator/index.php?option=com_media&path=local-images:/sampledata/cassiopeia');
    cy.wait('@getMedia');

    cy.get('.media-browser .media-browser-image').should('contain.text', 'nasa1-1200.jpg');
  });

  it('can display the local media data from the path in the session', () => {
    window.sessionStorage.setItem('joomla.mediamanager', JSON.stringify({ selectedDirectory: 'local-images:/sampledata/cassiopeia' }));
    cy.visit('/administrator/index.php?option=com_media');
    cy.wait('@getMedia');

    cy.get('.media-browser .media-browser-image').should('contain.text', 'nasa1-1200.jpg');
  });

  it('can display the local media data from the path in the url when also defined in the session', () => {
    window.sessionStorage.setItem('joomla.mediamanager', JSON.stringify({ selectedDirectory: 'local-images:/banners' }));
    cy.visit('/administrator/index.php?option=com_media&path=local-images:/sampledata/cassiopeia');
    cy.wait('@getMedia');

    cy.get('.media-browser .media-browser-image').should('contain.text', 'nasa1-1200.jpg');
  });

  it('can display an error message when an invalid path is defined in the url', () => {
    cy.visit('/administrator/index.php?option=com_media&path=local-images:/invalid');
    cy.wait('@getMedia');

    cy.checkForSystemMessage('File or Folder not found');
  });

  it('can display an error message when an invalid path is defined in the session', () => {
    window.sessionStorage.setItem('joomla.mediamanager', JSON.stringify({ selectedDirectory: 'local-images:/invalid' }));
    cy.visit('/administrator/index.php?option=com_media');
    cy.wait('@getMedia');

    cy.checkForSystemMessage('File or Folder not found');
  });
});
