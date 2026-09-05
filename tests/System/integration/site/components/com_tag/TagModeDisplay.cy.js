// The tag mode changes the shape of the URL and the tag form, never which items a tag page lists.

const parentTitle = 'Automated test display parent tag';
const childTitle = 'Automated test display child tag';
const parentArticleTitle = 'Article tagged with the display parent tag';
const childArticleTitle = 'Article tagged with the display child tag';

const setTagMode = (mode) => cy
  .task('queryDB', "SELECT params FROM #__extensions WHERE element = 'com_tags' AND type = 'component'")
  .then((rows) => {
    const params = JSON.parse(rows[0].params || '{}');

    if (mode === null) {
      delete params.mode;
    } else {
      params.mode = mode;
    }

    return cy.task(
      'queryDB',
      `UPDATE #__extensions SET params = '${JSON.stringify(params)}' WHERE element = 'com_tags' AND type = 'component'`,
    );
  });

describe('Test in frontend that the tag mode does not change which items a tag page lists', () => {
  const createdArticleIds = [];
  let parentTagId;
  let childTagId;

  before(() => {
    cy.task('queryDB', 'SELECT rgt FROM #__tags WHERE id = 1').then((rows) => {
      const rootRgt = rows[0].rgt;

      return cy.db_createTag({
        title: parentTitle,
        alias: 'automated-test-display-parent-tag',
        path: 'automated-test-display-parent-tag',
        parent_id: 1,
        level: 1,
        lft: rootRgt,
        rgt: rootRgt + 3,
      }).then((id) => {
        parentTagId = id;

        return cy.db_createTag({
          title: childTitle,
          alias: 'automated-test-display-child-tag',
          path: 'automated-test-display-parent-tag/automated-test-display-child-tag',
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
    setTagMode(null);

    createdArticleIds.forEach((id) => {
      cy.api_patch(`/content/articles/${id}`, { state: -2 });
      cy.api_delete(`/content/articles/${id}`);
    });

    cy.task('queryDB', `DELETE FROM #__contentitem_tag_map WHERE tag_id IN (${childTagId}, ${parentTagId})`);
    cy.task('queryDB', `DELETE FROM #__tags WHERE id IN (${childTagId}, ${parentTagId})`);
  });

  [null, 'flat', 'tree'].forEach((mode) => {
    const label = mode === null ? 'without the mode parameter' : `in ${mode} mode`;

    it(`can list only the items of the parent tag itself ${label}`, () => {
      setTagMode(mode);

      cy.visit(`/index.php?option=com_tags&view=tag&id[0]=${parentTagId}`);

      cy.contains(parentArticleTitle);
      cy.contains(childArticleTitle).should('not.exist');
    });

    it(`can list only the items of the child tag itself ${label}`, () => {
      setTagMode(mode);

      cy.visit(`/index.php?option=com_tags&view=tag&id[0]=${childTagId}`);

      cy.contains(childArticleTitle);
      cy.contains(parentArticleTitle).should('not.exist');
    });
  });
});
