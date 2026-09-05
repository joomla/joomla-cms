describe('Test that contenthistory for tag API endpoint', () => {
  beforeEach(() => {
    cy.task('queryDB', "DELETE FROM #__tags where title = 'automated test tag'");
    cy.task('queryDB', 'DELETE FROM #__history');
  });

  it('can get the history of an existing tag', () => {
  cy.api_post('/tags', {
    title: 'automated test tag', 
    parent_id: 1, 
    level: 1, 
    description: 'TEST', 
    language: '*',
  }).then((tag) => {
    const tagId = tag.body.data.attributes.id;
    
    // Update the tag to ensure history is created
    return cy.api_patch(`/tags/${tagId}`, {
      description: 'UPDATED TEST DESCRIPTION'
    }).then(() => tagId);
  }).then((tagId) => {
    // Now get history - should have entries
    return cy.api_get(`/tags/${tagId}/contenthistory?page=1&limit=20`);
  }).then((response) => {
    // Assert response status
    expect(response.status).to.eq(200);

    // Extract the `data` array
    const historyEntries = response.body.data;
    cy.log(`History Entries: ${historyEntries.length}`);
    
    // History should have at least one entry after update
    expect(historyEntries.length).to.be.gt(0);

    // Iterate through each history entry
    historyEntries.forEach((entry) => {
      const { attributes } = entry;

      // Access nested `version_data`
      const versionData = attributes.version_data;
      const tagTitle = versionData.title;

      // Perform assertions
      expect(attributes).to.have.property('editor_user_id');
      expect(versionData).to.have.property('title');
      expect(tagTitle).to.eq('automated test tag');
    });

    // Check the total pages from metadata
    const totalPages = response.body.meta['total-pages'];
    
    // This should now be 1 if we have history entries
    expect(totalPages).to.eq(1);
  });
});

  it('can delete the history of an existing tag', () => {
    cy.api_post('/tags', {
      title: 'automated test tag', parent_id: 1, level: 1, description: '', language: '*',
    })
      .then((tag) => cy.api_get(`/tags/${tag.body.data.attributes.id}/contenthistory`))
      .then((response) => {
        // Assert response status
        expect(response.status).to.eq(200);

        // Extract the `data` array
        const historyEntries = response.body.data;

        // Iterate through each history entry
        historyEntries.forEach((entry) => {
          // Access top-level attributes
          cy.api_delete(`/tags/${entry.id}/contenthistory`)
            .then((result) => expect(result.status).to.eq(204));
        });
      });
  });

  it('can keep the forever for the history of an existing tag', () => {
    cy.api_post('/tags', {
      title: 'automated test tag', parent_id: 1, level: 1, description: '', language: '*',
    })
      .then((tag) => cy.api_get(`/tags/${tag.body.data.attributes.id}/contenthistory`))
      .then((response) => {
        // Assert response status
        expect(response.status).to.eq(200);

        // Extract the `data` array
        const historyEntries = response.body.data;

        // Iterate through each history entry
        historyEntries.forEach((entry) => {
          // Access top-level attributes
          cy.api_patch(`/tags/${entry.id}/contenthistory/keep`, { keep_forever: 1 })
            .then((result) => expect(result.status).to.eq(200));
        });
      });
  });
});
