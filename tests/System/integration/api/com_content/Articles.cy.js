describe('Test that content API endpoint', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__content'));

  it('can deliver a list of articles', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then(() => cy.api_get('/content/articles'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test article'));
  });

  it('can deliver a list of unpublished articles', () => {
    cy.db_createArticle({ title: 'automated test article', state: 0 })
      .then(() => cy.api_get('/content/articles?filter[state]=0'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test article'));
  });

  it('can deliver a list of published articles', () => {
    cy.db_createArticle({ title: 'automated test article', state: 1 })
      .then(() => cy.api_get('/content/articles?filter[state]=1'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('title')
        .should('include', 'automated test article'));
  });

  it('can deliver a single article', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then((article) => cy.api_get(`/content/articles/${article.id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test article'));
  });

  it('can create an article', () => {
    cy.db_createCategory({ extension: 'com_content' })
      .then((categoryId) => cy.api_post('/content/articles', {
        title: 'automated test article',
        alias: 'test-article',
        catid: categoryId,
        introtext: '',
        fulltext: '',
        state: 1,
        access: 1,
        language: '*',
        created: '2023-01-01 20:00:00',
        modified: '2023-01-01 20:00:00',
        images: '',
        urls: '',
        attribs: '',
        metadesc: '',
        metadata: '',
      }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test article'));
  });

  it('can update an article', () => {
    cy.db_createArticle({ title: 'automated test article' })
      .then((article) => cy.api_patch(`/content/articles/${article.id}`, { title: 'updated automated test article' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'updated automated test article'));
  });

  it('can delete an article', () => {
    cy.db_createArticle({ title: 'automated test article', state: -2 })
      .then((article) => cy.api_delete(`/content/articles/${article.id}`));
  });

  it('should handle invalid payload formatting gracefully with a clean JSON error schema', () => {
    cy.api_getBearerToken().then((token) => {
      cy.request({
        method: 'POST',
        url: '/api/index.php/v1/content/articles',
        headers: {
          Authorization: `Bearer ${token}`,
        },
        body: {
          // Purposely malformed payload missing mandatory "data" wrap and required fields
          "attributes": {
            "title": "Malformed Test Article"
          }
        },
        failOnStatusCode: false
      })
      .then((response) => {
        // It should fail gracefully, not crash with a 500 Unhandled PHP Error
        expect(response.status).to.be.equal(400);

        // Validate
        expect(response.body).to.have.property('errors');
        expect(response.body.errors).to.be.an('array');
        expect(response.body.errors[0]).to.have.property('title');
        expect(response.body.errors[0].title).to.include('Field required: Category');
      });
    });
  });

  it('can deliver a list of articles conforming to page limit and offset pagination flags', () => {
    // Create 3 distinct articles to verify true middle-offset navigation
    cy.db_createArticle({ title: 'automated test article A' })
      .then(() => cy.db_createArticle({ title: 'automated test article B' }))
      .then(() => cy.db_createArticle({ title: 'automated test article C' }))
      .then(() => {
        // Verify Limit: Request only 2 items out of the 3 available
        return cy.api_get('/content/articles?page[limit]=2&page[offset]=0');
      })
      .then((response) => {
        cy.wrap(response).its('body').its('data').should('have.length', 2);
      })
      .then(() => {
        // Verify Offset: Limit to 1 item, but skip the first one (offset=1)
        return cy.api_get('/content/articles?page[limit]=1&page[offset]=1');
      })
      .then((response) => {
        // It must only return 1 item
        cy.wrap(response).its('body').its('data').should('have.length', 1);
        
        // Assert that it skipped 'C' (newest) and grabbed 'B' (middle)
        cy.wrap(response).its('body').its('data').its('0').its('attributes').its('title')
          .should('include', 'automated test article B');
      });
  });
});
