describe('Test in backend that the custom tags list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_tags&view=tags&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Tags');
  });

  it('can display a list of tags', () => {
    cy.db_createTag({ title: 'Test tag' }).then(() => {
      cy.reload();

      cy.contains('Test tag');
    });
  });

  it('can open the tag form', () => {
    cy.clickToolbarButton('New');

    cy.contains('Tags: New');
  });

  it('can publish the test tag', () => {
    cy.db_createTag({ title: 'Test tag', published: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test tag');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Publish').click();

      cy.checkForSystemMessage('Tag published');
    });
  });

  it('can unpublish the test tag', () => {
    cy.db_createTag({ title: 'Test tag', published: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test tag');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();

      cy.checkForSystemMessage('Tag unpublished');
    });
  });

  it('can trash the test tag', () => {
    cy.db_createTag({ title: 'Test tag' }).then(() => {
      cy.reload();
      cy.searchForItem('Test tag');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();

      cy.checkForSystemMessage('Tag trashed');
    });
  });

  it('can delete the test tag', () => {
    cy.db_createTag({ title: 'Test tag', published: -2 }).then(() => {
      cy.reload();
      cy.setFilter('published', 'Trashed');
      cy.searchForItem('Test tag');
      cy.checkAllResults();
      cy.clickToolbarButton('empty trash');
      cy.clickDialogConfirm(true);

      cy.checkForSystemMessage('Tag deleted');
    });
  });
});
