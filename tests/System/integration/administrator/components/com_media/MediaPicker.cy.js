describe('Test in backend that the media picker', () => {
  const testDirectory = 'images/test-media-picker-upload';
  const pickerPath = 'local-images:/test-media-picker-upload';
  const iframeSelector = '.joomla-dialog-media-field iframe';
  const fixturesFolder = Cypress.config('fixturesFolder').replace(/\\/g, '/');
  const projectRoot = Cypress.config('projectRoot').replace(/\\/g, '/');
  const mediaFixturesFolder = `${fixturesFolder.replace(projectRoot, '').replace(/^\//, '')}/com_media`;

  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.task('writeRelativeFile', { path: `${testDirectory}/folder-a/dummy.txt`, content: '1' });
    cy.task('writeRelativeFile', { path: `${testDirectory}/folder-b/dummy.txt`, content: '1' });
    cy.task('copyRelativeFile', {
      source: `${mediaFixturesFolder}/test-image-1.jpg`,
      destination: `${testDirectory}/folder-a/logo.jpg`,
    });
    cy.intercept('GET', '*task=api.files*').as('getMedia');
    cy.intercept('POST', '*task=api.files*').as('uploadMedia');
  });

  afterEach(() => {
    cy.task('deleteRelativePath', testDirectory);
  });

  it('selects the uploaded image when another folder contains the same filename', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.contains('[role="tab"]', 'Images and Links').click();
    cy.get('#jform_images_image_intro').closest('joomla-field-media').then((field) => {
      const url = new URL(field.attr('url'), field[0].ownerDocument.location.href);
      url.searchParams.set('path', `${pickerPath}/folder-a`);
      field.attr('url', url.toString());
    });
    cy.get('#jform_images_image_intro').closest('joomla-field-media').find('.button-select').click();
    cy.wait('@getMedia');

    cy.get(iframeSelector).iframe().within(() => {
      cy.contains('.media-browser-item', 'logo.jpg').should('exist');
      cy.contains('.media-breadcrumb a', 'test-media-picker-upload').click();
    });
    cy.wait('@getMedia');
    cy.get(iframeSelector).iframe().within(() => {
      cy.contains('.media-browser-item-directory', 'folder-b').dblclick();
    });
    cy.wait('@getMedia');

    cy.get(iframeSelector).iframe().within(() => {
      cy.get('input[type="file"]').selectFile({
        contents: `${Cypress.config('fixturesFolder')}/com_media/test-image-2.jpg`,
        fileName: 'logo.jpg',
        mimeType: 'image/jpeg',
      }, { force: true });
    });
    cy.wait('@uploadMedia').its('response.statusCode').should('eq', 200);
    cy.get(iframeSelector).iframe().within(() => {
      cy.contains('.media-browser-item.selected', 'logo.jpg').should('exist');
    });
    cy.window().its('Joomla.selectedMediaFile.path').should('eq', `${pickerPath}/folder-b/logo.jpg`);

    cy.contains('.joomla-dialog-media-field button', /^Select$/).click();
    cy.get('#jform_images_image_intro').invoke('val').should('include', `${testDirectory}/folder-b/logo.jpg`);
    cy.clickToolbarButton('Cancel');
  });
});
