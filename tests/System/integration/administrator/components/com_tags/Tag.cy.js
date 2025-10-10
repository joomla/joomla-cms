describe('Test in backend that the tag form', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    // Clear the filter
    cy.visit('/administrator/index.php?option=com_tags&filter=');
  });
  afterEach(() => cy.task('queryDB', "DELETE FROM #__tags WHERE title = 'Test tag'"));

  it('can create a tag', () => {
    cy.visit('/administrator/index.php?option=com_tags&task=tag.add');
    cy.get('#jform_title').clear().type('Test tag');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Tag saved');
    cy.contains('Test tag');
  });

  it('can edit a tag', () => {
    cy.db_createTag({ title: 'Test tag' }).then((id) => {
      cy.visit(`/administrator/index.php?option=com_tags&task=tag.edit&id=${id}`);
      cy.get('#jform_title').clear().type('Test tag edited');
      cy.clickToolbarButton('Save & Close');

      cy.contains('Test tag edited');
    });
  });

  it('check redirection to list view', () => {
    cy.visit('/administrator/index.php?option=com_tags&task=tag.add');
    cy.intercept('index.php?option=com_tags&view=tags').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
  });
});
