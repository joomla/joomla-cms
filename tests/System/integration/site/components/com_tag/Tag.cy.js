describe('Test in frontend that the tags tag view', () => {
  it('can display a list of tags in a menu item', () => {
    cy.db_createTag({ title: 'automated test tag 1' })
      .then(() => cy.db_createTag({ title: 'automated test tag 2' }))
      .then(() => cy.db_createTag({ title: 'automated test tag 3' }))
      .then(() => cy.db_createTag({ title: 'automated test tag 4' }))
      .then(() => cy.db_createMenuItem({ title: 'automated test tags', link: 'index.php?option=com_tags&view=tags' }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test tags)').click();

        cy.contains('automated test tag 1');
        cy.contains('automated test tag 2');
        cy.contains('automated test tag 3');
        cy.contains('automated test tag 4');
      });
  });

  it('can display a list of tags without a menu item', () => {
    cy.db_createTag({ title: 'automated test tag 1' })
      .then(() => cy.db_createTag({ title: 'automated test tag 2' }))
      .then(() => cy.db_createTag({ title: 'automated test tag 3' }))
      .then(() => cy.db_createTag({ title: 'automated test tag 4' }))
      .then(() => {
        cy.visit('/index.php?option=com_tags&view=tags');

        cy.contains('automated test tag 1');
        cy.contains('automated test tag 2');
        cy.contains('automated test tag 3');
        cy.contains('automated test tag 4');
      });
  });
});
