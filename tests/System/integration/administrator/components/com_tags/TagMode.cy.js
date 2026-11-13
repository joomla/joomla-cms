// The tag mode decides whether the tag form offers a parent, and it never changes any data.

const parentTitle = 'Automated test mode parent tag';
const childTitle = 'Automated test mode child tag';

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

describe('Test in backend that the tag mode decides the shape of the tag form', () => {
  let parentTagId;
  let childTagId;

  before(() => {
    // Insert the two tags as a valid nested set to the right of everything that already exists.
    cy.task('queryDB', 'SELECT rgt FROM #__tags WHERE id = 1').then((rows) => {
      const rootRgt = rows[0].rgt;

      return cy.db_createTag({
        title: parentTitle,
        alias: 'automated-test-mode-parent-tag',
        path: 'automated-test-mode-parent-tag',
        parent_id: 1,
        level: 1,
        lft: rootRgt,
        rgt: rootRgt + 3,
      }).then((id) => {
        parentTagId = id;

        return cy.db_createTag({
          title: childTitle,
          alias: 'automated-test-mode-child-tag',
          path: 'automated-test-mode-parent-tag/automated-test-mode-child-tag',
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
  });

  after(() => {
    setTagMode(null);
    cy.task('queryDB', `DELETE FROM #__tags WHERE id IN (${childTagId}, ${parentTagId})`);
  });

  beforeEach(() => cy.doAdministratorLogin());

  it('offers the parent field without the mode parameter', () => {
    setTagMode(null);

    cy.visit(`/administrator/index.php?option=com_tags&task=tag.edit&id=${childTagId}`);

    cy.get('#jform_parent_id').should('exist');
  });

  it('offers the parent field in tree mode', () => {
    setTagMode('tree');

    cy.visit(`/administrator/index.php?option=com_tags&task=tag.edit&id=${childTagId}`);

    cy.get('#jform_parent_id').should('exist');
  });

  it('does not offer the parent field in flat mode', () => {
    setTagMode('flat');

    cy.visit(`/administrator/index.php?option=com_tags&task=tag.edit&id=${childTagId}`);

    cy.get('#jform_title').should('exist');
    cy.get('#jform_parent_id').should('not.exist');
  });

  it('keeps the parent of a nested tag when it is saved in flat mode', () => {
    setTagMode('flat');

    cy.visit(`/administrator/index.php?option=com_tags&task=tag.edit&id=${childTagId}`);
    cy.clickToolbarButton('Save & Close');
    cy.checkForSystemMessage('Tag saved');

    // The mode never rewrites data, so the tag still hangs below its parent rather than below the root.
    cy.task('queryDB', `SELECT parent_id, level FROM #__tags WHERE id = ${childTagId}`).then((rows) => {
      expect(rows[0].parent_id).to.eq(parentTagId);
      expect(rows[0].level).to.eq(2);
    });
  });

  it('creates a new tag below the root in flat mode', () => {
    setTagMode('flat');

    cy.visit('/administrator/index.php?option=com_tags&task=tag.add');
    cy.get('#jform_title').clear().type('Automated test mode new tag');
    cy.clickToolbarButton('Save & Close');
    cy.checkForSystemMessage('Tag saved');

    cy.task('queryDB', "SELECT parent_id FROM #__tags WHERE title = 'Automated test mode new tag'").then((rows) => {
      expect(rows[0].parent_id).to.eq(1);
    });

    cy.task('queryDB', "DELETE FROM #__tags WHERE title = 'Automated test mode new tag'");
  });
});
