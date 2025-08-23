describe('Test that contenthistory for banners API endpoint', () => {
  beforeEach(() => {
    cy.task('queryDB', 'DELETE FROM #__banners');
    cy.task('queryDB', 'DELETE FROM #__history');
  });

  it('can get the history of an existing banner', () => {
    cy.db_createCategory({ extension: 'com_banners' })
      .then((categoryId) => cy.api_post('/banners', {
        name: 'automated test banner',
        alias: 'test-banner',
        catid: categoryId,
        state: 1,
        language: '*',
        description: '',
        custombannercode: '',
        params: {
          imageurl: '', width: '', height: '', alt: '',
        },
      }))
      .then((banner) => cy.api_get(`/banners/${banner.body.data.attributes.id}/contenthistory`))
      .then((response) => {
        // Assert response status
        expect(response.status).to.eq(200);

        // Extract the `data` array
        const { data: historyEntries } = response.body;
        cy.log(`History Entries: ${historyEntries.length}`);

        // Iterate through each history entry
        historyEntries.forEach((entry) => {
          const { attributes } = entry;

          // Access top-level attributes
          const historyId = entry.id;
          const saveDate = attributes.save_date;
          const { editor } = attributes;
          const characterCount = attributes.character_count;

          // Access nested `version_data`
          const versionData = attributes.version_data;
          const bannerName = versionData.name;
          const { alias } = versionData;
          const createdDate = versionData.created;
          const modifiedDate = versionData.modified;

          // Log details for debugging
          cy.log(`History ID: ${historyId}`);
          cy.log(`Save Date: ${saveDate}`);
          cy.log(`Editor: ${editor}`);
          cy.log(`Character Count: ${characterCount}`);
          cy.log(`Banner Name: ${bannerName}`);
          cy.log(`Alias: ${alias}`);
          cy.log(`Created Date: ${createdDate}`);
          cy.log(`Modified Date: ${modifiedDate}`);

          // Perform assertions
          expect(attributes).to.have.property('editor_user_id');
          expect(versionData).to.have.property('name');
          expect(versionData).to.have.property('modified');
          expect(bannerName).to.eq('automated test banner');
        });

        // Check the total pages from metadata
        const totalPages = response.body.meta['total-pages'];
        expect(totalPages).to.eq(1);
        cy.log(`Total Pages: ${totalPages}`);
      });
  });
});
