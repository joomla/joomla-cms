import { AST_CATEGORY_TITLE } from '../../../support/constants.mjs';

describe('Test that contenthistory for content API endpoint', () => {
  beforeEach(() => {
    cy.task('queryDB', `DELETE FROM #__categories WHERE title LIKE '${AST_CATEGORY_TITLE}%'`);
    cy.task('queryDB', 'DELETE FROM #__content');
    cy.task('queryDB', 'DELETE FROM #__history');
  });

  it('can get the history of an existing article', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} history`, extension: 'com_content', parent_id: 1, published: 1 })
      .then((catRes) => cy.api_post('/content/articles', {
        title: 'automated test article',
        alias: 'test-article',
        catid: Number(catRes.body.data.id),
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
      .then((article) => cy.api_get(`/content/articles/${article.body.data.attributes.id}/contenthistory`))
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
          const articleTitle = versionData.title;
          const { alias } = versionData;
          const createdDate = versionData.created;

          // Log details for debugging
          cy.log(`History ID: ${historyId}`);
          cy.log(`Save Date: ${saveDate}`);
          cy.log(`Editor: ${editor}`);
          cy.log(`Character Count: ${characterCount}`);
          cy.log(`Article Title: ${articleTitle}`);
          cy.log(`Alias: ${alias}`);
          cy.log(`Created Date: ${createdDate}`);

          // Perform assertions
          expect(attributes).to.have.property('editor_user_id');
          expect(versionData).to.have.property('title');
          expect(articleTitle).to.eq('automated test article');
        });

        // Check the total pages from metadata
        const totalPages = response.body.meta['total-pages'];
        expect(totalPages).to.eq(1);
        cy.log(`Total Pages: ${totalPages}`);
      });
  });

  it('can delete the history of an existing article', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} history delete`, extension: 'com_content', parent_id: 1, published: 1 })
      .then((catRes) => cy.api_post('/content/articles', {
        title: 'automated test article',
        alias: 'test-article',
        catid: Number(catRes.body.data.id),
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
      .then((article) => cy.api_get(`/content/articles/${article.body.data.attributes.id}/contenthistory`))
      .then((response) => {
        // Assert response status
        expect(response.status).to.eq(200);
        // Extract the `data` array
        const { data: historyEntries } = response.body;
        cy.log(`History Entries: ${historyEntries.length}`);

        // Iterate through each history entry
        historyEntries.forEach((entry) => {
          // const { attributes } = entry;

          // Access top-level attributes
          // const historyId = entry.id;
          cy.api_delete(`/content/articles/${entry.id}/contenthistory`)
            .then((result) => cy.wrap(result).its('status').should('equal', 204));
        });
      });
  });
});
