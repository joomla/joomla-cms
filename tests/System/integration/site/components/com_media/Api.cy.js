describe('Test that the media site API endpoint', () => {
  // Create relative path to the fixtures images directory, from Cypress config and platform independent
  const fixturesFolder = Cypress.config('fixturesFolder').replace(/\\/g, '/');
  const projectRoot = Cypress.config('projectRoot').replace(/\\/g, '/');
  const mediaFixturesFolder = `${fixturesFolder
    .replace(projectRoot, '')
    .replace(/^\//, '')}/com_media`;

  const dir = 'images/test-site-api';
  const url = '/index.php?option=com_media&task=api.files&format=json&mediatypes=0,1,2,3&path=local-images:/test-site-api';

  let csrfToken;
  let newContent;

  /**
   * Posts a file as the logged in user to the media site API.
   *
   * @param {object} body The request body
   *
   * @returns Chainable
   */
  const postFile = (body) => cy.request({
    method: 'POST',
    url,
    body: { content: newContent, ...body },
    headers: { 'X-CSRF-Token': csrfToken },
    failOnStatusCode: false,
  });

  beforeEach(() => {
    cy.task('writeRelativeFile', { path: `${dir}/dummy.txt`, content: '1' });
    cy.task('copyRelativeFile', { source: `${mediaFixturesFolder}/test-image-1.jpg`, destination: `${dir}/target.jpg` });

    cy.readFile(`${mediaFixturesFolder}/test-image-2.jpg`, 'base64').then((data) => {
      newContent = data;
    });

    // An author has core.create, but not core.edit, on com_media
    cy.db_createUser({ group_id: 3, username: 'mediaauthor', email: 'mediaauthor@example.com' }).then(() => {
      cy.doFrontendLogin('mediaauthor', 'test', false);
    });

    cy.visit('/index.php?option=com_media&view=media&tmpl=component');
    cy.window().then((win) => {
      csrfToken = win.Joomla.getOptions('csrf.token');
    });
  });

  afterEach(() => {
    cy.task('deleteRelativePath', dir);
  });

  it('does not allow a user without core.edit to override an existing file', () => {
    postFile({ name: 'target.jpg', override: false }).its('status').should('eq', 409);
    postFile({ name: 'target.jpg', override: true }).its('status').should('eq', 403);
  });

  it('does not allow a user without core.edit to override an existing file with a name which is normalised by the adapter', () => {
    ['target!.jpg', 'target#.jpg', 'target.JPG'].forEach((name) => {
      postFile({ name }).its('status').should('eq', 409);
    });

    // The original file is untouched
    cy.readFile(`${dir}/target.jpg`, 'base64').then((current) => {
      cy.readFile(`${mediaFixturesFolder}/test-image-1.jpg`, 'base64').should('eq', current);
    });
  });

  it('does not allow a user without core.edit to use override on a new file', () => {
    postFile({ name: 'new.jpg', override: true }).its('status').should('eq', 403);
    cy.readFile(`${dir}/new.jpg`, { timeout: 500 }).should('not.exist');
  });

  it('allows a user without core.edit to create a new file', () => {
    postFile({ name: 'new.jpg' }).its('status').should('eq', 200);
    cy.readFile(`${dir}/new.jpg`, 'base64').should('eq', newContent);
  });

  it('normalises the name of a new file', () => {
    postFile({ name: 'new image!.JPG' }).its('status').should('eq', 200);
    cy.readFile(`${dir}/new image.jpg`, 'base64').should('eq', newContent);
  });
});
