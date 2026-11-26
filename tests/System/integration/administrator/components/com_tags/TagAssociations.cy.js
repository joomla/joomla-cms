// Tags can be associated across content languages once the language filter plugin has item associations enabled.

const enTitle = 'Automated test association tag EN';
const deTitle = 'Automated test association tag DE';

// Insert a tag as a valid nested set node directly below the root, to the right of everything that already exists.
const createTag = (title, language) => cy.task('queryDB', 'SELECT rgt FROM #__tags WHERE id = 1').then((rows) => {
  const rootRgt = rows[0].rgt;
  const alias = title.toLowerCase().replace(/ /g, '-');

  return cy.db_createTag({
    title,
    alias,
    path: alias,
    parent_id: 1,
    level: 1,
    lft: rootRgt,
    rgt: rootRgt + 1,
    language,
  }).then((id) => cy.task('queryDB', `UPDATE #__tags SET rgt = ${rootRgt + 2} WHERE id = 1`).then(() => id));
});

const associationsOf = (id) => cy.task(
  'queryDB',
  `SELECT a2.id FROM #__associations AS a1 INNER JOIN #__associations AS a2 ON a1.\`key\` = a2.\`key\` AND a2.context = a1.context WHERE a1.context = 'com_tags.tag' AND a1.id = ${id} ORDER BY a2.id`,
);

const associate = (ids) => {
  const key = `cypress-tags-${ids.join('-')}`;

  return cy.task('queryDB', `INSERT INTO #__associations (id, context, \`key\`) VALUES ${ids.map((id) => `(${id}, 'com_tags.tag', '${key}')`).join(', ')}`);
};

// The association fields are fancy selects, so pick the option through the Choices.js dropdown.
const chooseAssociation = (langCode, title) => {
  cy.get(`#jform_associations_${langCode.replace('-', '_')}`).closest('joomla-field-fancy-select').as('field');
  cy.get('@field').find('.choices').click();
  cy.get('@field').find('.choices__list--dropdown .choices__item').contains(title).click();
};

describe('Test in backend that tag associations', () => {
  let enId;
  let deId;
  let rootRgt;

  // The global cleanup removes the inserted tags and the content language after every test, so they are created per test.
  beforeEach(() => {
    cy.task('queryDB', 'SELECT rgt FROM #__tags WHERE id = 1').then((rows) => {
      rootRgt = rows[0].rgt;
    });

    // A second published content language. Associations only need the content language, not an installed language pack.
    cy.db_createLanguage({ lang_code: 'de-DE', ordering: 2 });
    cy.db_enableExtension('1', 'plg_system_languagefilter');

    createTag(enTitle, 'en-GB').then((id) => {
      enId = id;
    });
    createTag(deTitle, 'de-DE').then((id) => {
      deId = id;
    });

    cy.doAdministratorLogin();
  });

  afterEach(() => {
    cy.db_enableExtension('0', 'plg_system_languagefilter');
    cy.task('queryDB', `DELETE FROM #__associations WHERE context = 'com_tags.tag' AND id IN (${enId}, ${deId})`);
    cy.task('queryDB', `UPDATE #__tags SET rgt = ${rootRgt} WHERE id = 1`);
  });

  it('are not offered while the language filter plugin is disabled', () => {
    cy.db_enableExtension('0', 'plg_system_languagefilter');
    cy.visit(`/administrator/index.php?option=com_tags&task=tag.edit&id=${enId}`);

    cy.get('div[role="tablist"] button[aria-controls="associations"]').should('not.exist');
    cy.get('#jform_associations_de_DE').should('not.exist');
    cy.clickToolbarButton('Cancel');

    cy.visit('/administrator/index.php?option=com_tags&view=tags&filter=');
    cy.get('#tagList thead').should('not.contain.text', 'Association');
  });

  it('offer an associations tab with a field per content language', () => {
    cy.visit(`/administrator/index.php?option=com_tags&task=tag.edit&id=${enId}`);
    cy.get('div[role="tablist"] button[aria-controls="associations"]').click();

    cy.get('#jform_associations_de_DE').should('exist');
    cy.get('#jform_associations_en_GB').should('exist');
    cy.clickToolbarButton('Cancel');
  });

  it('only offer tags in the language of the association field', () => {
    cy.visit(`/administrator/index.php?option=com_tags&task=tag.edit&id=${enId}`);
    cy.get('div[role="tablist"] button[aria-controls="associations"]').click();

    cy.get('#jform_associations_de_DE').closest('joomla-field-fancy-select').as('field');
    cy.get('@field').find('.choices').click();
    cy.get('@field').find('.choices__list--dropdown').should('contain.text', deTitle).and('not.contain.text', enTitle);
    cy.clickToolbarButton('Cancel');
  });

  it('can be created from the tag form', () => {
    cy.visit(`/administrator/index.php?option=com_tags&task=tag.edit&id=${enId}`);
    cy.get('div[role="tablist"] button[aria-controls="associations"]').click();
    chooseAssociation('de-DE', deTitle);
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Tag saved');
    associationsOf(enId).then((rows) => {
      expect(rows.map((row) => row.id)).to.deep.equal([enId, deId].sort((a, b) => a - b));
    });
  });

  it('are preselected in the tag form', () => {
    associate([enId, deId]);
    cy.visit(`/administrator/index.php?option=com_tags&task=tag.edit&id=${enId}`);
    cy.get('div[role="tablist"] button[aria-controls="associations"]').click();

    cy.get('#jform_associations_de_DE').should('have.value', `${deId}`);
    cy.clickToolbarButton('Cancel');
  });

  it('can be removed from the tag form', () => {
    associate([enId, deId]);
    cy.visit(`/administrator/index.php?option=com_tags&task=tag.edit&id=${enId}`);
    cy.get('div[role="tablist"] button[aria-controls="associations"]').click();
    cy.get('#jform_associations_de_DE').closest('joomla-field-fancy-select').find('.choices__list--single .choices__button_joomla').click({ force: true });
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Tag saved');
    associationsOf(enId).then((rows) => expect(rows).to.have.length(0));
    associationsOf(deId).then((rows) => expect(rows).to.have.length(0));
  });

  it('are shown in the tags list', () => {
    associate([enId, deId]);
    cy.visit('/administrator/index.php?option=com_tags&view=tags&filter=');
    cy.searchForItem(deTitle);

    cy.get('#tagList thead').should('contain.text', 'Association');
    cy.get('#tagList tbody tr').first().find(`a.badge[href*="task=tag.edit&id=${enId}"]`).should('contain.text', 'en-GB');
  });

  it('are listed in the multilingual associations component', () => {
    associate([enId, deId]);
    cy.visit('/administrator/index.php?option=com_associations&view=associations&itemtype=com_tags.tag&language=en-GB');

    cy.contains('tr', enTitle).should('contain.text', 'de-DE');
  });

  it('can be edited side by side in the multilingual associations component', () => {
    associate([enId, deId]);
    cy.visit(`/administrator/index.php?option=com_associations&view=association&layout=edit&itemtype=com_tags.tag&task=association.edit&id=${enId}&target=de-DE:${deId}:edit`);

    cy.get('#reference-association').should('have.attr', 'data-id', `${enId}`);
    cy.get('#target-association').should('have.attr', 'data-id', `${deId}`);
    cy.clickToolbarButton('Close');
  });
});
