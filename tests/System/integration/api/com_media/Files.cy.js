describe('Test that media files API endpoint', () => {
  // Ensure 'test-dir' (relative to cmsPath) is available and has correct permissions
  beforeEach(() => cy.task('writeRelativeFile', { path: 'images/test-dir/dummy.txt', content: '1' }));
  // If it exists, delete the 'test-dir' (relative to cmsPath) and its contents
  afterEach(() => cy.task('deleteRelativePath', 'images/test-dir'));

  it('can deliver a list of files', () => {
    cy.api_get('/media/files')
      .then((response) => {
        cy.api_responseContains(response, 'name', 'banners');
        cy.api_responseContains(response, 'name', 'joomla_black.png');
      });
  });

  it('can deliver a list of files in a subfolder', () => {
    cy.api_get('/media/files/sampledata/cassiopeia/')
      .then((response) => cy.api_responseContains(response, 'name', 'nasa1-1200.jpg'));
  });

  it('can deliver a list of files with an adapter', () => {
    cy.api_get('/media/files/local-images:/sampledata/cassiopeia/')
      .then((response) => cy.api_responseContains(response, 'name', 'nasa1-1200.jpg'));
  });

  it('can search in filenames', () => {
    cy.api_get('/media/files?filter[search]=joomla')
      .then((response) => {
        cy.api_responseContains(response, 'name', 'joomla_black.png');
        cy.wrap(response).its('body').its('data').should('have.length', 1);
      });
  });

  it('can deliver a single file', () => {
    cy.api_get('/media/files/joomla_black.png')
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'joomla_black.png'));
  });

  it('can deliver a single file with the url', () => {
    cy.api_get('/media/files/joomla_black.png?url=1')
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('url')
        .should('include', 'joomla_black.png'));
  });

  it('can deliver a single folder', () => {
    cy.api_get('/media/files/sampledata/cassiopeia')
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'cassiopeia'));
  });

  it('can create a file without adapter', () => {
    cy.readFile('tests/System/data/com_media/test-image-1.jpg', 'binary')
      .then((data) => cy.api_post('/media/files', { path: 'test-dir/test.jpg', content: Buffer.from(data, 'binary').toString('base64') }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'test.jpg');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-images:/test-dir/test.jpg');
      });
  });

  it('can create a folder without adapter', () => {
    cy.api_post('/media/files', { path: 'test-dir/test-from-create' })
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'test-from-create');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-images:/test-dir/test-from-create');
      });
  });

  it('can create a file with adapter', () => {
    cy.readFile('tests/System/data/com_media/test-image-1.jpg', 'binary')
      .then((data) => cy.api_post('/media/files', { path: 'local-images:/test-dir/test.jpg', content: Buffer.from(data, 'binary').toString('base64') }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'test.jpg');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-images:/test-dir/test.jpg');
      });
  });

  it('can create a folder with adapter', () => {
    cy.api_post('/media/files', { path: 'local-images:/test-dir/test-from-create' })
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'test-from-create');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-images:/test-dir/test-from-create');
      });
  });

  it('can update a file without adapter', () => {
    cy.task('writeRelativeFile', { path: 'images/test-dir/override.jpg', content: '1' })
      .then(() => cy.readFile('tests/System/data/com_media/test-image-1.jpg', 'binary'))
      .then((data) => cy.api_patch(
        '/media/files/test-dir/override.jpg',
        { path: 'test-dir/override.jpg', content: Buffer.from(data, 'binary').toString('base64') },
      )).then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'override.jpg');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-images:/test-dir/override.jpg');
      });
  });

  it('can update a folder without adapter', () => {
    cy.task('writeRelativeFile', { path: 'images/test-dir/override/test.jpg', content: '1' })
      .then(() => cy.api_patch('/media/files/test-dir/override', { path: 'test-dir/override-new' }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'override-new');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-images:/test-dir/override-new');
      });
  });

  it('can update a file with adapter', () => {
    cy.task('writeRelativeFile', { path: 'images/test-dir/override.jpg', content: '1' })
      .then(() => cy.readFile('tests/System/data/com_media/test-image-1.jpg', 'binary'))
      .then((data) => cy.api_patch(
        '/media/files/local-images:/test-dir/override.jpg',
        { path: 'local-images:/test-dir/override.jpg', content: Buffer.from(data, 'binary').toString('base64') },
      )).then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'override.jpg');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-images:/test-dir/override.jpg');
      });
  });

  it('can update a folder with adapter', () => {
    cy.task('writeRelativeFile', { path: 'images/test-dir/override/test.jpg', content: '1' })
      .then(() => cy.api_patch('/media/files/local-images:/test-dir/override', { path: 'local-images:/test-dir/override-new' }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'override-new');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-images:/test-dir/override-new');
      });
  });

  it('can delete a file without adapter', () => {
    cy.task('writeRelativeFile', { path: 'images/test-dir/todelete.jpg', content: '1' })
      .then(() => cy.api_delete('/media/files/test-dir/todelete.jpg'));
  });

  it('can delete a folder without adapter', () => {
    cy.task('writeRelativeFile', { path: 'images/test-dir/todelete/dummy.txt', content: '1' })
      .then(() => cy.api_delete('/media/files/test-dir/todelete'));
  });

  it('can delete a file with adapter', () => {
    cy.task('writeRelativeFile', { path: 'images/test-dir/todelete.jpg', content: '1' })
      .then(() => cy.api_delete('/media/files/local-images:/test-dir/todelete.jpg'));
  });

  it('can delete a folder with adapter', () => {
    cy.task('writeRelativeFile', { path: 'images/test-dir/todelete/dummy.txt', content: '1' })
      .then(() => cy.api_delete('/media/files/local-images:/test-dir/todelete'));
  });
});
