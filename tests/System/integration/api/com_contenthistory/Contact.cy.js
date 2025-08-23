describe('Test that contenthistory for contact API endpoint', () => {
  beforeEach(() => {
    cy.task('queryDB', 'DELETE FROM #__contact_details');
    cy.task('queryDB', 'DELETE FROM #__history');
  });

  it('can get the history of an existing contact', () => {
    cy.db_createCategory({ extension: 'com_contact' })
      .then((categoryId) => cy.api_post('/contacts', {
        name: 'automated test contact',
        alias: 'test-contact',
        catid: categoryId,
        published: 1,
        language: '*',
      }))
      .then((contact) => cy.api_get(`/contacts/${contact.body.data.attributes.id}/contenthistory`))
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
          const contactName = versionData.name;
          const { alias } = versionData;
          const createdDate = versionData.created;
          const modifiedDate = versionData.modified;

          // Log details for debugging
          cy.log(`History ID: ${historyId}`);
          cy.log(`Save Date: ${saveDate}`);
          cy.log(`Editor: ${editor}`);
          cy.log(`Character Count: ${characterCount}`);
          cy.log(`Contact Name: ${contactName}`);
          cy.log(`Alias: ${alias}`);
          cy.log(`Created Date: ${createdDate}`);
          cy.log(`Modified Date: ${modifiedDate}`);

          // Perform assertions
          expect(attributes).to.have.property('editor_user_id');
          expect(versionData).to.have.property('name');
          expect(versionData).to.have.property('modified');
          expect(contactName).to.eq('automated test contact');
        });

        // Check the total pages from metadata
        const totalPages = response.body.meta['total-pages'];
        expect(totalPages).to.eq(1);
        cy.log(`Total Pages: ${totalPages}`);
      });
  });
});
