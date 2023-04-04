describe('Test that the breadcrumbs module', () => {
  it('can load in frontend and displays breadcrumb items', () => {
    cy.db_createModule({ module: 'mod_breadcrumbs' }).then(() => {
      cy.visit('/');

      cy.contains('li', 'You are here:');
    });
  });
});
