beforeEach(() => {
  // Ensure test dir is available and has correct permissions
  cy.task('writeFile', { path: 'images/test-dir/dummy.txt', content: '1' });
});

afterEach(() => {
  cy.task('deleteFolder', 'images/test-dir');
});

describe('Test that media files API endpoint', () => {
  it('can create a file without adapter', () => {
    cy.readFile('tests/cypress/data/com_media/test-image-1.jpg', 'binary')
      .then((data) => cy.api_post('/media/files', { path: 'test-dir/test.jpg', content: Buffer.from(data, 'binary').toString('base64') }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes').its('name').should('include', 'test.jpg');
        cy.wrap(response).its('body').its('data').its('attributes').its('path').should('include', 'local-images:/test-dir/test.jpg');
      });
  });

  it('can create a folder without adapter', () => {
    cy.api_post('/media/files', { path: 'test-dir/test-from-create' })
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes').its('name').should('include', 'test-from-create');
        cy.wrap(response).its('body').its('data').its('attributes').its('path').should('include', 'local-images:/test-dir/test-from-create');
      });
  });

  it('can create a file with adapter', () => {
    cy.readFile('tests/cypress/data/com_media/test-image-1.jpg', 'binary')
      .then((data) => cy.api_post('/media/files', { path: 'local-images:/test-dir/test.jpg', content: Buffer.from(data, 'binary').toString('base64') }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes').its('name').should('include', 'test.jpg');
        cy.wrap(response).its('body').its('data').its('attributes').its('path').should('include', 'local-images:/test-dir/test.jpg');
      });
  });

  it('can create a folder with adapter', () => {
    cy.api_post('/media/files', { path: 'local-images:/test-dir/test-from-create' })
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes').its('name').should('include', 'test-from-create');
        cy.wrap(response).its('body').its('data').its('attributes').its('path').should('include', 'local-images:/test-dir/test-from-create');
      });
  });

  it('can update a file without adapter', () => {
    cy.task('writeFile', { path: 'images/test-dir/override.jpg', content: '1' })
      .then(() => cy.readFile('tests/cypress/data/com_media/test-image-1.jpg', 'binary'))
      .then((data) => cy.api_patch(
        '/media/files/test-dir/override.jpg',
        { path: 'test-dir/override.jpg', content: Buffer.from(data, 'binary').toString('base64') })
      ).then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes').its('name').should('include', 'override.jpg');
        cy.wrap(response).its('body').its('data').its('attributes').its('path').should('include', 'local-images:/test-dir/override.jpg');
      });
  });

  it('can update a folder without adapter', () => {
    cy.task('writeFile', { path: 'images/test-dir/override/test.jpg', content: '1' })
      .then(() => cy.api_patch('/media/files/test-dir/override', { path: 'test-dir/override-new' }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes').its('name').should('include', 'override-new');
        cy.wrap(response).its('body').its('data').its('attributes').its('path').should('include', 'local-images:/test-dir/override-new');
      });
  });

  it('can update a file with adapter', () => {
    cy.task('writeFile', { path: 'images/test-dir/override.jpg', content: '1' })
      .then(() => cy.readFile('tests/cypress/data/com_media/test-image-1.jpg', 'binary'))
      .then((data) => cy.api_patch(
        '/media/files/local-images:/test-dir/override.jpg',
        { path: 'local-images:/test-dir/override.jpg', content: Buffer.from(data, 'binary').toString('base64') })
      ).then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes').its('name').should('include', 'override.jpg');
        cy.wrap(response).its('body').its('data').its('attributes').its('path').should('include', 'local-images:/test-dir/override.jpg');
      });
  });

  it('can update a folder with adapter', () => {
    cy.task('writeFile', { path: 'images/test-dir/override/test.jpg', content: '1' })
      .then(() => cy.api_patch('/media/files/local-images:/test-dir/override', { path: 'local-images:/test-dir/override-new' }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes').its('name').should('include', 'override-new');
        cy.wrap(response).its('body').its('data').its('attributes').its('path').should('include', 'local-images:/test-dir/override-new');
      });
  });

  it('can delete a file without adapter', () => {
    cy.task('writeFile', { path: 'images/test-dir/todelete.jpg', content: '1' })
      .then(() => cy.api_delete( '/media/files/test-dir/todelete.jpg' ));
  });

  it('can delete a folder without adapter', () => {
    cy.task('writeFile', { path: 'images/test-dir/todelete/dummy.txt', content: '1' })
      .then(() => cy.api_delete( '/media/files/test-dir/todelete' ));
  });

  it('can delete a file with adapter', () => {
    cy.task('writeFile', { path: 'images/test-dir/todelete.jpg', content: '1' })
      .then(() => cy.api_delete( '/media/files/local-images:/test-dir/todelete.jpg' ));
  });

  it('can delete a folder with adapter', () => {
    cy.task('writeFile', { path: 'images/test-dir/todelete/dummy.txt', content: '1' })
      .then(() => cy.api_delete( '/media/files/local-images:/test-dir/todelete' ));
  });
});
