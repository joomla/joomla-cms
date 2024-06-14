describe('Test that contacts API endpoint', () => {
  beforeEach(() => cy.task('clearEmails'));
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__contact_details'));

  it('can deliver a list of contacts', () => {
    cy.db_createContact({ name: 'automated test contact' })
      .then(() => cy.api_get('/contacts'))
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('name')
        .should('include', 'automated test contact'));
  });

  it('can deliver a single contact', () => {
    cy.db_createContact({ name: 'automated test contact' })
      .then((contact) => cy.api_get(`/contacts/${contact.id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'automated test contact'));
  });

  it('can create a contact', () => {
    cy.db_createCategory({ extension: 'com_contact' })
      .then((categoryId) => cy.api_post('/contacts', {
        name: 'automated test contact',
        alias: 'test-contact',
        catid: categoryId,
        published: 1,
        language: '*',
      }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'automated test contact'));
  });

  it('can update a contact', () => {
    cy.db_createContact({ name: 'automated test contact', access: 1 })
      .then((contact) => cy.api_patch(`/contacts/${contact.id}`, { name: 'updated automated test contact' }))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('name')
        .should('include', 'updated automated test contact'));
  });

  it('can delete a contact', () => {
    cy.db_createContact({ name: 'automated test contact', published: -2 })
      .then((contact) => cy.api_delete(`/contacts/${contact.id}`));
  });

  it('can submit a contact form', () => {
    cy.db_getUserId().then((id) => cy.db_createContact({ name: 'automated test contact', user_id: id, params: '{"show_email_form":"1"}' }))
      .then((contact) => cy.api_post(`/contacts/form/${contact.id}`, {
        contact_name: Cypress.env('name'),
        contact_email: Cypress.env('email'),
        contact_subject: 'automated test subject',
        contact_message: 'automated test message',
      }))
      .then((response) => cy.wrap(response).its('status')
        .should('equal', 200));

    cy.task('getMails').then((mails) => {
      expect(mails.length).to.equal(1);
      cy.wrap(mails[0].sender).should('equal', Cypress.env('email'));
      cy.wrap(mails[0].receivers).should('have.property', Cypress.env('email'));
      cy.wrap(mails[0].headers.subject).should('equal', `${Cypress.env('sitename')}: automated test subject`);
      cy.wrap(mails[0].body).should('have.string', 'This is an enquiry email via');
      cy.wrap(mails[0].body).should('have.string', `${Cypress.env('name')} <${Cypress.env('email')}>`);
      cy.wrap(mails[0].body).should('have.string', 'automated test message');
    });
  });
});
