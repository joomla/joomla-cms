describe('Test in frontend that the users registration view', () => {
  beforeEach(() => cy.db_updateExtensionParameter('allowUserRegistration', '1', 'com_users'));
  afterEach(() => cy.db_updateExtensionParameter('allowUserRegistration', '0', 'com_users'));

  it('can display a registration form for a test user without a menu item', () => {
    cy.visit('/index.php?option=com_users&view=registration');

    cy.get('.com-users-registration__form').should('contain.text', 'User Registration');
  });

  it('can display a registration form for a test user in a menu item', () => {
    cy.db_createMenuItem({ title: 'Automated test registration', link: 'index.php?option=com_users&view=registration' })
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(Automated test registration)').click();

        cy.get('.com-users-registration__form').should('contain.text', 'User Registration');
      });
  });
});
