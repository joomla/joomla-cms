describe('Test that field users API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__viewlevels where title = 'automated test level'"));

  it('can deliver a list of levels', () => {
    cy.api_get('/users/levels')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'Public'));
  });

  it('can deliver a single level', () => {
    cy.api_get('/users/levels/1')
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'Public'));
  });

  it('can create a level', () => {
    cy.api_post('/users/levels', {
      id: '0',
      title: 'automated test level',
      rules: '[1]',
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test level'));
  });

  it('can patch a level', () => {
      // First create a level we can PATCH
      cy.api_post('/users/levels', {
        id: '0',
        title: 'automated test level',
        rules: '[1]', // valid initial rules
      }).then((createResponse) => {
        const createdId = createResponse.body.data.id;
      
        // Now try to PATCH it with valid rules payload
        cy.api_patch(`/users/levels/${createdId}`, {
          title: 'automated test level',
          rules: '[1,2]',
        }).then((patchResponse) => {
          expect(patchResponse.status).to.eq(200);
          cy.wrap(patchResponse).its('body').its('data').its('attributes').its('rules')
            .then((rules) => {
              if (typeof rules === 'string') {
                const normalized = rules.replace(/\s+/g, '');
                expect(normalized).to.eq('[1,2]');
              } else {
                const nums = rules.map((r) => (typeof r === 'string' ? Number(r) : r));
                expect(nums).to.deep.equal([1, 2]);
              }
            });
        });
      });
    });
});
