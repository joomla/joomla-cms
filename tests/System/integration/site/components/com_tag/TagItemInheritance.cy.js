// Characterization test: a tag page lists only the items that carry that tag directly.
//
// The follow-up work that introduces a tag mode keeps this behaviour in every mode, so this test has to
// survive that change unmodified. If it ever needs editing, the behaviour changed.

const parentTitle = 'Automated test parent tag';
const childTitle = 'Automated test child tag';
const parentArticleTitle = 'Article tagged with the parent tag';
const childArticleTitle = 'Article tagged with the child tag';

describe('Test in frontend that a tag page does not inherit items from child tags', () => {
  const createdArticleIds = [];
  let parentTagId;
  let childTagId;

  before(() => {
    /**
     * Insert the two tags as a proper nested set to the right of everything that already exists, so the
     * tree stays valid for any code that reads lft and rgt. db_createTag writes the row as given and
     * does not make room for it by itself.
     */
    cy.task('queryDB', 'SELECT rgt FROM #__tags WHERE id = 1').then((rows) => {
      const rootRgt = rows[0].rgt;

      return cy.db_createTag({
        title: parentTitle,
        alias: 'automated-test-parent-tag',
        path: 'automated-test-parent-tag',
        parent_id: 1,
        level: 1,
        lft: rootRgt,
        rgt: rootRgt + 3,
      }).then((id) => {
        parentTagId = id;

        return cy.db_createTag({
          title: childTitle,
          alias: 'automated-test-child-tag',
          path: 'automated-test-parent-tag/automated-test-child-tag',
          parent_id: parentTagId,
          level: 2,
          lft: rootRgt + 1,
          rgt: rootRgt + 2,
        });
      }).then((id) => {
        childTagId = id;

        return cy.task('queryDB', `UPDATE #__tags SET rgt = ${rootRgt + 4} WHERE id = 1`);
      });
    });

    // The articles are created through the API so that the tag map and the ucm records are written the
    // same way the administrator writes them.
    cy.db_createCategory({ extension: 'com_content' }).then((categoryId) => {
      const article = (title, tagId) => ({
        title,
        catid: categoryId,
        introtext: `<p>${title}</p>`,
        fulltext: '',
        state: 1,
        access: 1,
        language: '*',
        tags: [String(tagId)],
        created: '2023-01-01 20:00:00',
        modified: '2023-01-01 20:00:00',
        images: '',
        urls: '',
        attribs: '',
        metadesc: '',
        metadata: '',
      });

      cy.api_post('/content/articles', article(parentArticleTitle, parentTagId))
        .then((response) => createdArticleIds.push(response.body.data.id));

      cy.api_post('/content/articles', article(childArticleTitle, childTagId))
        .then((response) => createdArticleIds.push(response.body.data.id));
    });
  });

  after(() => {
    createdArticleIds.forEach((id) => {
      // An article has to be trashed before the API will delete it.
      cy.api_patch(`/content/articles/${id}`, { state: -2 });
      cy.api_delete(`/content/articles/${id}`);
    });

    cy.task('queryDB', `DELETE FROM #__contentitem_tag_map WHERE tag_id IN (${childTagId}, ${parentTagId})`);
    cy.task('queryDB', `DELETE FROM #__tags WHERE id IN (${childTagId}, ${parentTagId})`);
  });

  it('can list only the items tagged with the parent tag itself', () => {
    cy.visit(`/index.php?option=com_tags&view=tag&id[0]=${parentTagId}`);

    cy.contains(parentArticleTitle);

    // The article that only carries the child tag must not appear on the parent tag page.
    cy.contains(childArticleTitle).should('not.exist');
  });

  it('can list only the items tagged with the child tag itself', () => {
    cy.visit(`/index.php?option=com_tags&view=tag&id[0]=${childTagId}`);

    cy.contains(childArticleTitle);

    // Tagging does not travel down the tree either.
    cy.contains(parentArticleTitle).should('not.exist');
  });
});
