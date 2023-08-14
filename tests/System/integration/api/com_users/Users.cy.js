describe('Test that users API endpoint', () => {
  afterEach(() => cy.task('queryDB', " DELETE FROM #__users WHERE name = 'automated test user' "));
  it('can deliver a list of users', () => {
    cy.db_createUser({ name: 'automated test user', username: 'automated test username' })
      .then(() => cy.api_get('/users'))
      .then((response) => cy.wrap(response).its('body').its('data.2').its('attributes')
        .its('name')
        .should('include', 'automated test user'));
  });

  it('can create a user', () => {
    cy.api_post('/users', {
      block: '0',
      email: 'test1@mail.com',
      groups: [
        '2',
      ],
      id: '0',
      lastResetTime: '',
      lastvisitDate: '',
      name: 'automated test user',
      params: {
        admin_language: '',
        admin_style: '',
        editor: '',
        helpsite: '',
        language: '',
        timezone: '',
      },
      password: 'qwertyqwerty123',
      password2: 'qwertyqwerty123',
      registerDate: '',
      requireReset: '0',
      resetCount: '0',
      sendEmail: '0',
      username: 'automated test username',
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'automated test user'));
  });

  it('can update a user', () => {
    cy.db_createUser({ name: 'automated test user' })
      .then((id) => {
        const updatedUserData = {
          name: 'updated automated test user',
          groups: [
            '2',
          ],
        };
        return cy.api_patch(`/users/${id}`, updatedUserData);
      })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'updated automated test user'));
  });

  it('can delete a user', () => {
    cy.db_createUser({ name: 'automated test user' })
      .then((id) => cy.api_delete(`/users/${id}`));
  });
});
