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
    cy.on('uncaught:exception', () => false);
    cy.visit('/administrator/index.php?option=com_media&path=local-images:/invalid');
    cy.wait('@getMedia');

    cy.checkForSystemMessage('File or Folder not found');
  });

  it('can display an error message when an invalid path is defined in the session', () => {
    cy.on('uncaught:exception', () => false);
    window.sessionStorage.setItem('joomla.mediamanager', JSON.stringify({ selectedDirectory: 'local-images:/invalid' }));
    cy.visit('/administrator/index.php?option=com_media');
    cy.wait('@getMedia');

    cy.checkForSystemMessage('File or Folder not found');
  });

  it('can not rename to malicious file', () => {
    cy.visit('/administrator/index.php?option=com_media');
    cy.wait('@getMedia');

    cy.window()
      .then((win) => win.Joomla.getOptions('csrf.token'))
      .then((token) => cy.request({
        method: 'put',
        url: '/administrator/index.php?option=com_media&format=json&mediatypes=0,1,2,3&task=api.files&path=local-images%3A%2Fpowered_by.png',
        body: { [token]: '1', newPath: 'local-images:/powered.php', move: 0 },
        failOnStatusCode: false,
      }))
      .then((response) => {
        expect(response.status).to.eq(500);
        cy.readFile('images/powered.php').should('not.exist');
      });
  });

  it('can upload the same file to two folders in one session', () => {
    const testDirectory = 'images/test-upload-same-file';
    const upload = () => cy.get('input[type="file"]').selectFile({
      contents: `${Cypress.config('fixturesFolder')}/com_media/test-image-1.jpg`,
      fileName: 'same-file.jpg',
      mimeType: 'image/jpeg',
    }, { force: true });

    cy.task('deleteRelativePath', testDirectory);
    cy.task('writeRelativeFile', { path: `${testDirectory}/folder-a/dummy.txt`, content: '1' });
    cy.task('writeRelativeFile', { path: `${testDirectory}/folder-b/dummy.txt`, content: '1' });
    cy.intercept('POST', '*task=api.files*').as('uploadMedia');

    cy.visit('/administrator/index.php?option=com_media&path=local-images:/test-upload-same-file/folder-a');
    cy.wait('@getMedia');
    upload();
    cy.wait('@uploadMedia').its('response.statusCode').should('eq', 200);
    cy.get('.media-browser').should('contain.text', 'same-file.jpg');

    // The upload field must be empty again, otherwise the browser fires no change event for the same file
    cy.get('input[type="file"]').should('have.value', '');

    // Switch the folder without reloading the page
    cy.contains('.media-breadcrumb a', 'test-upload-same-file').click();
    cy.wait('@getMedia');
    cy.contains('.media-browser-item-directory', 'folder-b').dblclick();
    cy.wait('@getMedia');
    upload();
    cy.wait('@uploadMedia').its('response.statusCode').should('eq', 200);
    cy.get('input[type="file"]').should('have.value', '');

    cy.request('/administrator/index.php?option=com_media&task=api.files&format=json&path=local-images:/test-upload-same-file/folder-b')
      .then((response) => cy.wrap(response.body.data.map((item) => item.name)).should('include', 'same-file.jpg'));

    cy.task('deleteRelativePath', testDirectory);
  });

  it('can not traverse up in search', () => {
    cy.request('/administrator/index.php?option=com_media&task=api.files&format=json&path=local-images:/&search=/../../*.txt&recursive=0&content=1&mediatypes=0,1,2,3')
      .then((response) => cy.wrap(response.body.data).should('be.empty'));
  });
});
