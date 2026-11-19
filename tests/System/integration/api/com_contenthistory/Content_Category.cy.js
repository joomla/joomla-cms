describe('Test that contenthistory for content category API endpoint', () => {
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__categories WHERE title = 'automated test content category'");
    cy.task('queryDB', 'DELETE FROM #__history');
  });

  it('can get the history of an existing article category', () => {
    cy.api_post('/content/categories', {
      title: 'automated test content category',
      description: 'automated test content category description',
      parent_id: 1,
      extension: 'com_content',
    })
      .then((category) => cy.api_get(`/content/category/${category.body.data.attributes.id}/contenthistory`))
      .then((response) => {
        // Assert response status
        expect(response.status).to.eq(200);

        // Extract the `data` array
        const historyEntries = response.body.data;

        // Iterate through each history entry
        historyEntries.forEach((entry) => {
          const { attributes } = entry;

          // Access nested `version_data`
          const versionData = attributes.version_data;
          const categoryTitle = versionData.title;

          // Perform assertions
          expect(attributes).to.have.property('editor_user_id');
          expect(versionData).to.have.property('title');
          expect(categoryTitle).to.eq('automated test content category');
        });

        // Check the total pages from metadata
        const totalPages = response.body.meta['total-pages'];
        expect(totalPages).to.eq(1);
      });
  });

  it('can delete the history of an existing article category', () => {
    cy.api_post('/content/categories', {
      title: 'automated test content category',
      description: 'automated test content category description',
      parent_id: 1,
      extension: 'com_content',
    })
      .then((category) => cy.api_get(`/content/category/${category.body.data.attributes.id}/contenthistory`))
      .then((response) => {
        // Assert response status
        expect(response.status).to.eq(200);

        // Extract the `data` array
        const historyEntries = response.body.data;

        // Iterate through each history entry
        historyEntries.forEach((entry) => {
          // Access top-level attributes
          cy.api_delete(`/content/category/${entry.id}/contenthistory`)
            .then((result) => expect(result.status).to.eq(204));
        });
      });
  });

  it('can keep the forever for the history of an existing article category', () => {
    cy.api_post('/content/categories', {
      title: 'automated test content category',
      description: 'automated test content category description',
      parent_id: 1,
      extension: 'com_content',
    })
      .then((category) => cy.api_get(`/content/category/${category.body.data.attributes.id}/contenthistory`))
      .then((response) => {
        // Assert response status
        expect(response.status).to.eq(200);

        // Extract the `data` array
        const historyEntries = response.body.data;

        // Iterate through each history entry
        historyEntries.forEach((entry) => {
          // Access top-level attributes
          cy.api_patch(`/content/category/${entry.id}/contenthistory/keep`, { keep_forever: 1 })
            .then((result) => expect(result.status).to.eq(200));
        });
      });
  });
});
