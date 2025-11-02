describe('Test that media files API endpoint', () => {
  // Create relative path to the fixtures images directory, from Cypress config and platform independent
  const fixturesFolder = Cypress.config('fixturesFolder').replace(/\\/g, '/');
  // projectRoot is e.g. 'C:\laragon\www\joomla53\tests\System\fixtures'
  const projectRoot = Cypress.config('projectRoot').replace(/\\/g, '/');
  // Result is e.g. 'tests/System/fixtures/com_media'
  const mediaFixturesFolder = `${fixturesFolder
    .replace(projectRoot, '')
    .replace(/^\//, '')}/com_media`;

  // Create directories and test images before running each test
  beforeEach(() => {
    // Ensure 'files/test-dir' exists (relative to cmsPath) and has the correct permissions
    cy.task('writeRelativeFile', { path: 'files/test-dir/dummy.txt', content: '1' });
    // Ensure 'images/test-dir2' exists (relative to cmsPath) and has the correct permissions
    cy.task('writeRelativeFile', { path: 'images/test-dir2/dummy.txt', content: '1' });
    cy.task('copyRelativeFile', { source: `${mediaFixturesFolder}/test-image-1.jpg`, destination: 'files/test-image-1.jpg' });
    cy.task('copyRelativeFile', { source: `${mediaFixturesFolder}/test-image-2.jpg`, destination: 'files/test-dir/test-image-2.jpg' });
    cy.task('copyRelativeFile', { source: `${mediaFixturesFolder}/test-image-3.jpg`, destination: 'images/test-dir2/test-image-3.jpg' });
  });
  // Delete all files and directories created during the test, only for clean-up and only if they exist
  after(() => {
    cy.task('deleteRelativePath', 'files/test-dir');
    cy.task('deleteRelativePath', 'images/test-dir2');
    cy.task('deleteRelativePath', 'files/test-image-1.jpg');
  });

  it('can deliver a list of files', () => {
    cy.api_get('/media/files')
      .then((response) => {
        cy.api_responseContains(response, 'name', 'test-dir');
        cy.api_responseContains(response, 'name', 'test-image-1.jpg');
      });
  });

  it('can deliver a list of files in a subfolder', () => {
    cy.api_get('/media/files/test-dir/')
      .then((response) => cy.api_responseContains(response, 'name', 'test-image-2.jpg'));
  });

  it('can deliver a list of files with an adapter', () => {
    cy.api_get('/media/files/local-images:/sampledata/cassiopeia/')
      .then((response) => cy.api_responseContains(response, 'name', 'nasa1-1200.jpg'));
  });

  it('can search in filenames', () => {
    cy.api_get('/media/files/local-images:/?filter[search]=joomla')
      .then((response) => {
        cy.api_responseContains(response, 'name', 'joomla_black.png');
        cy.wrap(response).its('body').its('data').should('have.length', 1);
      });
  });

  it('can deliver a single file', () => {
    cy.api_get('/media/files/local-images:/joomla_black.png')
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'joomla_black.png'));
  });

  it('can deliver a single file with the url', () => {
    cy.api_get('/media/files/local-images:/joomla_black.png?url=1')
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('url')
        .should('include', 'joomla_black.png'));
  });

  it('can deliver a single folder', () => {
    cy.api_get('/media/files/local-images:/sampledata/cassiopeia')
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'cassiopeia'));
  });

  it('can create a file without adapter', () => {
    cy.readFile('tests/System/fixtures/com_media/test-image-1.jpg', 'binary')
      .then((data) => cy.api_post('/media/files', { path: 'test-dir/test.jpg', content: Buffer.from(data, 'binary').toString('base64') }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'test.jpg');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-files:/test-dir/test.jpg');
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
          .should('include', 'local-files:/test-dir/test-from-create');
      });
  });

  it('can create a file with adapter', () => {
    cy.readFile('tests/System/fixtures/com_media/test-image-2.jpg', 'binary')
      .then((data) => cy.api_post('/media/files', { path: 'local-images:/test-dir2/test.jpg', content: Buffer.from(data, 'binary').toString('base64') }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'test.jpg');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-images:/test-dir2/test.jpg');
      });
  });

  it('can create a folder with adapter', () => {
    cy.api_post('/media/files', { path: 'local-images:/test-dir2/test-from-create' })
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'test-from-create');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-images:/test-dir2/test-from-create');
      });
  });

  it('can update a file without adapter', () => {
    cy.task('writeRelativeFile', { path: 'files/test-dir/override.jpg', content: '1', mode: 0o666 })
      .then(() => cy.readFile('tests/System/fixtures/com_media/test-image-1.jpg', 'binary'))
      .then((data) => cy.api_patch(
        '/media/files/test-dir/override.jpg',
        { path: 'test-dir/override.jpg', content: Buffer.from(data, 'binary').toString('base64') },
      )).then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'override.jpg');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-files:/test-dir/override.jpg');
      });
  });

  it('can update a folder without adapter', () => {
    cy.task('writeRelativeFile', { path: 'files/test-dir/override/test.jpg', content: '1', mode: 0o666 })
      .then(() => cy.api_patch('/media/files/test-dir/override', { path: 'test-dir/override-new' }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'override-new');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-files:/test-dir/override-new');
      });
  });

  it('can update a file with adapter', () => {
    cy.task('writeRelativeFile', { path: 'images/test-dir2/override.jpg', content: '1', mode: 0o666 })
      .then(() => cy.readFile('tests/System/fixtures/com_media/test-image-2.jpg', 'binary'))
      .then((data) => cy.api_patch(
        '/media/files/local-images:/test-dir2/override.jpg',
        { path: 'local-images:/test-dir2/override.jpg', content: Buffer.from(data, 'binary').toString('base64') },
      )).then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'override.jpg');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-images:/test-dir2/override.jpg');
      });
  });

  it('can update a folder with adapter', () => {
    cy.task('writeRelativeFile', { path: 'images/test-dir2/override/test.jpg', content: '1', mode: 0o666 })
      .then(() => cy.api_patch('/media/files/local-images:/test-dir2/override', { path: 'local-images:/test-dir2/override-new' }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('name')
          .should('include', 'override-new');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('path')
          .should('include', 'local-images:/test-dir2/override-new');
      });
  });

  it("can delete an image in 'files' without adapter", () => {
    cy.api_delete('/media/files/test-dir/test-image-2.jpg');
  });

  it("can delete a folder in 'files' without adapter", () => {
    cy.api_delete('/media/files/test-dir');
  });

  it("can delete an image in 'images' with adapter", () => {
    cy.api_delete('/media/files/local-images:/test-dir2/test-image-3.jpg');
  });

  it("can delete a folder in 'images' with adapter", () => {
    cy.api_delete('/media/files/local-images:/test-dir2');
  });
});
