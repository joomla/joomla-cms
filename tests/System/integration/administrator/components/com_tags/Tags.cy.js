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
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Tag published').should('exist');
    });
  });

  it('can unpublish the test tag', () => {
    cy.db_createTag({ title: 'Test tag', published: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test tag');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Tag unpublished').should('exist');
    });
  });

  it('can trash the test tag', () => {
    cy.db_createTag({ title: 'Test tag' }).then(() => {
      cy.reload();
      cy.searchForItem('Test tag');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Tag trashed').should('exist');
    });
  });

  it('can delete the test tag', () => {
    cy.db_createTag({ title: 'Test tag', published: -2 }).then(() => {
      cy.reload();
      cy.setFilter('published', 'Trashed');
      cy.searchForItem('Test tag');
      cy.checkAllResults();
      cy.clickToolbarButton('empty trash');
      cy.on('window:confirm', () => true);

      cy.get('#system-message-container').contains('Tag deleted').should('exist');
    });
  });
});
