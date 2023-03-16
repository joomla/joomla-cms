describe('Test that media files API endpoint', () => {
  it('can deliver a list of files', () => {
    cy.api_get('/media/files')
      .then((response) => {
        cy.wrap(response).its('body').its('data.1').its('attributes').its('name').should('include', 'banners');
        cy.wrap(response).its('body').its('data.5').its('attributes').its('name').should('include', 'joomla_black.png');
      });
  });

  it('can deliver a list of files in a subfolder', () => {
    cy.api_get('/media/files/sampledata/cassiopeia/')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes').its('name').should('include', 'nasa1-1200.jpg'));
  });

  it('can deliver a list of files wih an adapter', () => {
    cy.api_get('/media/files/local-images:/sampledata/cassiopeia/')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes').its('name').should('include', 'nasa1-1200.jpg'));
  });

  it('can search in files', () => {
    cy.api_get('/media/files?filter[search]=joomla')
      .then((response) => {
        cy.wrap(response).its('body').its('data.0').its('attributes').its('name').should('include', 'joomla_black.png');
        cy.wrap(response).its('body').its('data').should('have.length', 1);
      });
  });

  it('can deliver a single file', () => {
    cy.api_get('/media/files/joomla_black.png')
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes').its('name').should('include', 'joomla_black.png'));
  });

  it('can deliver a single file with the url', () => {
    cy.api_get('/media/files/joomla_black.png?url=1')
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes').its('url').should('include', 'joomla_black.png'));
  });

  it('can deliver a single folder', () => {
    cy.api_get('/media/files/sampledata/cassiopeia')
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes').its('name').should('include', 'cassiopeia'));
  });
});
