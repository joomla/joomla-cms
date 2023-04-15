describe('Test in frondend that the breadcrumbs module', () => {
  it('can display the breadcrumb items', () => {
    cy.db_createModule({ module: 'mod_breadcrumbs' }).then(() => {
      cy.visit('/');

      cy.contains('li', 'You are here:');
    });
  });
});
