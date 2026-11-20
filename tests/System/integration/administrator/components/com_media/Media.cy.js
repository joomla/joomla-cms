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

  ['joomla_black.png', 'banners'].forEach((name) => {
    it(`rejects an empty name when renaming ${name}`, () => {
      cy.visit('/administrator/index.php?option=com_media&path=local-images:/');
      cy.wait('@getMedia');
      cy.intercept('PUT', '*format=json*task=api.files*').as('renameMedia');

      cy.contains('.media-browser-item', name).find('.action-toggle .image-browser-action').click({ force: true });
      cy.contains('.media-browser-item', name).find('.action-rename').click();
      cy.get('[role="dialog"]').within(() => {
        cy.get('#name').clear();
        cy.get('.modal-footer .btn-success').should('be.disabled');
        cy.get('#name').type('{enter}').should('have.value', '');
        cy.get('form').submit();
        cy.get('#name').should('have.value', '');
        cy.get('#name').type('   ');
        cy.get('.modal-footer .btn-success').should('be.disabled');
        cy.get('#name').type('{enter}');
        cy.get('form').submit();
        cy.get('#name').should('have.value', '   ');
        cy.get('#name').clear().type('valid-name');
        cy.get('.modal-footer .btn-success').should('be.enabled');
        cy.contains('button', 'Cancel').click();
      });
      cy.get('@renameMedia.all').should('have.length', 0);

      cy.contains('.media-browser-item', name).find('.action-toggle .image-browser-action').click({ force: true });
      cy.contains('.media-browser-item', name).find('.action-rename').click();
      cy.get('#name').should('have.value', name === 'joomla_black.png' ? 'joomla_black' : name);
      cy.contains('[role="dialog"] button', 'Cancel').click();
    });
  });

  describe('rename error recovery', () => {
    const items = [
      { name: 'test-48883-rename.png', newName: 'test-48883-renamed-image', extension: '.png' },
      { name: 'test-48883-rename-folder', newName: 'test-48883-renamed-folder', extension: '' },
    ];

    afterEach(() => {
      items.forEach((item) => {
        cy.task('deleteRelativePath', `images/${item.name}`);
        cy.task('deleteRelativePath', `images/${item.newName}${item.extension}`);
      });
    });

    items.forEach((item) => {
      it(`shows server errors inside the dialog and allows retrying ${item.name}`, () => {
        if (item.extension) {
          cy.task('copyRelativeFile', { source: 'images/joomla_black.png', destination: `images/${item.name}` });
        } else {
          cy.task('writeRelativeFile', { path: `images/${item.name}/keep.txt`, content: 'Rename test' });
        }

        cy.visit('/administrator/index.php?option=com_media&path=local-images:/');
        cy.wait('@getMedia');
        cy.intercept(
          { method: 'PUT', url: '*format=json*task=api.files*', times: 1 },
          { statusCode: 400, body: { success: false, message: 'Filename rejected by the server.' } },
        ).as('renameRejected');

        cy.contains('.media-browser-item', item.name).find('.action-toggle .image-browser-action').click({ force: true });
        cy.contains('.media-browser-item', item.name).find('.action-rename').click();
        cy.get('[aria-labelledby="renameTitle"]').within(() => {
          cy.get('#name').clear().type('        rejected-name');
          cy.get('.modal-footer .btn-success').click();
        });
        cy.wait('@renameRejected').its('request.body.newPath').should('eq', `local-images:/        rejected-name${item.extension}`);

        cy.get('[aria-labelledby="renameTitle"]').within(() => {
          cy.get('#renameFeedback').should('be.visible').and('contain.text', 'Filename rejected by the server.');
          cy.get('#name').should('have.value', '        rejected-name').and('have.attr', 'aria-invalid', 'true');
          cy.get('.modal-footer .btn-success').should('be.enabled');
          cy.get('#name').clear().type(item.newName);
          cy.get('#renameFeedback').should('not.exist');
        });

        cy.intercept('PUT', '*format=json*task=api.files*').as('renameRetry');
        cy.get('#name').type('{enter}');
        cy.wait('@renameRetry').its('response.statusCode').should('eq', 200);
        cy.get('[aria-labelledby="renameTitle"]').should('not.exist');
        cy.contains('.media-browser-item', `${item.newName}${item.extension}`).should('be.visible');
      });
    });
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

  it('can not traverse up in search', () => {
    cy.request('/administrator/index.php?option=com_media&task=api.files&format=json&path=local-images:/&search=/../../*.txt&recursive=0&content=1&mediatypes=0,1,2,3')
      .then((response) => cy.wrap(response.body.data).should('be.empty'));
  });
});
