describe('Test in frontend that the contact form view', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__contact_details'));

  it('can create a contact through a form', () => {
    cy.doFrontendLogin();
    cy.visit('/index.php?option=com_contact&view=form&layout=edit');
    cy.get('#jform_name').type('test contact 1');
    cy.get('.mb-2 > .btn-primary').click();

    cy.task('queryDB', 'SELECT catid FROM #__contact_details WHERE name = \'test contact 1\'').then((id) => {
      cy.visit(`/index.php?option=com_contact&view=category&id=${id[0].catid}`);

      cy.contains('test contact 1').should('exist');
    });
  });

  it('can send an email on contact form submission ', () => {
    cy.task('clearEmails');
    cy.db_getUserId().then((id) => cy.db_createContact({ name: 'test contact', user_id: id }))
      .then((contact) => {
        cy.visit(`/index.php?option=com_contact&view=contact&id=${contact.id}`);
        cy.get('#jform_contact_name').type('Test User');
        cy.get('#jform_contact_email').type('testuser@example.com');
        cy.get('#jform_contact_emailmsg').type('Test Subject');
        cy.get('#jform_contact_message').type('Test message content');
        cy.get('button.btn.btn-primary.validate[type="submit"]').click();

        cy.task('getMails').then((mails) => {
          expect(mails.length).to.be.greaterThan(0);
          cy.wrap(mails[0].body).should('contain', 'Test message content');
        });
      });
  });
});
