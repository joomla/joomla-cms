describe('Test in frontend that the contact form module', () => {
  const formSelector = 'form[data-mod-contact-form]';

  const createModuleForContact = (contactId, params = {}) => cy.db_createModule({
    title: 'automated test contact form module',
    module: 'mod_contact_form',
    params: JSON.stringify({ contact_id: contactId, ...params }),
  });

  const fillRequiredFields = () => {
    cy.get('input[name="jform[contact_name]"]').type('Automated Test User');
    cy.get('input[name="jform[contact_email]"]').type('test@example.com');
    cy.get('input[name="jform[contact_subject]"]').type('Module submit test');
    cy.get('textarea[name="jform[contact_message]"]').type('This is an automated submit test.');
  };

  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__modules WHERE module = 'mod_contact_form'");
    cy.task('queryDB', "DELETE FROM #__modules_menu WHERE moduleid NOT IN (SELECT id FROM #__modules)");
    cy.task('queryDB', "DELETE FROM #__contact_details WHERE name IN ('automated test contact form contact', 'automated test tampered contact')");
  });

  it('renders the form when configured with a published contact', () => {
    cy.db_createContact({ name: 'automated test contact form contact' })
      .then((contact) => createModuleForContact(contact.id))
      .then(() => {
        cy.visit('/');

        cy.get(formSelector).should('exist');
        cy.get('.mod-contact-form__btn-submit').should('exist');
      });
  });

  it('does not render the form when contact is unpublished', () => {
    cy.db_createContact({
      name: 'automated test contact form contact',
      published: 0,
    })
      .then((contact) => createModuleForContact(contact.id))
      .then(() => {
        cy.visit('/');

        cy.get(formSelector).should('not.exist');
      });
  });

  it('rejects tampered contact_id submissions', () => {
    let expectedContactId;
    let tamperedContactId;

    cy.db_createContact({ name: 'automated test contact form contact' })
      .then((contact) => {
        expectedContactId = contact.id;

        return cy.db_createContact({ name: 'automated test tampered contact' });
      })
      .then((tamperedContact) => {
        tamperedContactId = tamperedContact.id;

        return createModuleForContact(expectedContactId);
      })
      .then(() => {
        cy.visit('/');

        cy.get(formSelector).should('exist');
        cy.intercept('POST', '**/index.php?option=com_ajax&module=contact_form&method=submit&format=json').as('submitForm');

        fillRequiredFields();

        cy.get('input[name="contact_id"]').should('have.value', `${expectedContactId}`);
        cy.get('input[name="contact_id"]').invoke('val', `${tamperedContactId}`);

        cy.get(`${formSelector} .mod-contact-form__btn-submit`).click();

        cy.wait('@submitForm').its('response.body.data.ok').should('eq', false);
        cy.get('.mod-contact-form__result .alert-danger').should('exist');
      });
  });

  it('shows success in the result container without replacing the form', () => {
    let moduleId;

    cy.db_createContact({ name: 'automated test contact form contact' })
      .then((contact) => createModuleForContact(contact.id))
      .then((id) => {
        moduleId = id;

        cy.visit('/');

        cy.get(formSelector).should('exist');

        cy.intercept('POST', '**/index.php?option=com_ajax&module=contact_form&method=submit&format=json', {
          statusCode: 200,
          body: {
            success: true,
            data: {
              ok: true,
              instanceId: moduleId,
              message: 'Automated success message',
              token: '0123456789abcdef0123456789abcdef',
            },
          },
        }).as('submitForm');

        fillRequiredFields();

        cy.get(`${formSelector} .mod-contact-form__btn-submit`).click();

        cy.wait('@submitForm');
        cy.get('.mod-contact-form__result .alert-success').should('contain.text', 'Automated success message');
        cy.get(formSelector).should('exist');
        cy.get('input[name="jform[contact_name]"]').should('have.value', '');
      });
  });
});
