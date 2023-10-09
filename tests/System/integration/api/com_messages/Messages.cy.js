describe('Test that messages API endpoint', () => {
  afterEach(() => cy.task('queryDB', "DELETE FROM #__users WHERE username = 'automated_test_username'"));

  it('can create a message', () => {
    cy.api_post('/users', {
      block: '0',
      email: 'test1@mail.com',
      groups: [
        '8',
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
      sendEmail: '1',
      username: 'automated_test_username',
    })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('id')
        .then((id) => cy.api_post('/messages', {
          user_id_from: id,
          user_id_to: 3,
          state: 0,
          subject: 'subject automated test',
          message: `message automated test${id}`,
        })))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('message')
        .should('include', 'message automated test'));
  });

  it('can deliver a list of messages', () => {
    cy.api_get('/messages')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('subject')
        .should('include', 'subject automated test'));
  });

  it('can deliver a single message', () => {
    cy.api_get('/messages')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id'))
      .then((id) => {
        cy.api_get(`/messages/${id}`)
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('subject')
            .should('include', 'subject automated test'));
      });
  });

  it('can modify a single message', () => {
    cy.api_get('/messages')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id'))
      .then((id) => {
        const updateMessage = {
          state: 1,
        };
        cy.api_patch(`/messages/${id}`, updateMessage)
          .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
            .its('state')
            .should('equal', 1));
      });
  });

  it('can delete a message', () => {
    cy.api_get('/messages')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('id'))
      .then((id) => cy.api_delete(`/messages/${id}`))
      .then((response) => cy.wrap(response).its('status').should('equal', 204));
  });
});
