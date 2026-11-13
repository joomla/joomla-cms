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

  it('can create a contact with multiple secondary categories', () => {
    let contactId = 0;
    let secondaryCategoryId1 = 0;
    let secondaryCategoryId2 = 0;

    cy.db_createCategory({
      title: 'api primary category',
      alias: 'api-primary-category-multi',
      path: 'api-primary-category-multi',
      extension: 'com_contact',
    })
      .then((primaryCategoryId) => cy.db_createCategory({
        title: 'api secondary category 1',
        alias: 'api-secondary-category-1',
        path: 'api-secondary-category-1',
        extension: 'com_contact',
      }).then((categoryId) => {
        secondaryCategoryId1 = categoryId;

        return cy.db_createCategory({
          title: 'api secondary category 2',
          alias: 'api-secondary-category-2',
          path: 'api-secondary-category-2',
          extension: 'com_contact',
        });
      }).then((categoryId) => {
        secondaryCategoryId2 = categoryId;

        return cy.api_post('/contacts', {
          name: 'automated multiple secondary categories contact',
          alias: 'automated-multiple-secondary-categories-contact',
          catid: primaryCategoryId,
          secondary_categories: [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ],
          published: 1,
          access: 1,
          language: '*',
        });
      }))
      .then((response) => {
        contactId = response.body.data.id;

        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ]);

        return cy.api_get(`/contacts/${contactId}`);
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ]);
      });
  });

  it('can replace secondary categories for a contact', () => {
    let contactId = 0;
    let secondaryCategoryId1 = 0;
    let secondaryCategoryId2 = 0;

    cy.db_createCategory({
      title: 'api primary category',
      alias: 'api-primary-category-replace',
      path: 'api-primary-category-replace',
      extension: 'com_contact',
    })
      .then((primaryCategoryId) => cy.db_createCategory({
        title: 'api secondary category 1',
        alias: 'api-secondary-category-replace-1',
        path: 'api-secondary-category-replace-1',
        extension: 'com_contact',
      }).then((categoryId) => {
        secondaryCategoryId1 = categoryId;

        return cy.db_createCategory({
          title: 'api secondary category 2',
          alias: 'api-secondary-category-replace-2',
          path: 'api-secondary-category-replace-2',
          extension: 'com_contact',
        });
      }).then((categoryId) => {
        secondaryCategoryId2 = categoryId;

        return cy.api_post('/contacts', {
          name: 'replace secondary categories contact',
          alias: 'replace-secondary-categories-contact',
          catid: primaryCategoryId,
          secondary_categories: [secondaryCategoryId1],
          published: 1,
          access: 1,
          language: '*',
        });
      }))
      .then((response) => {
        contactId = response.body.data.id;

        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [secondaryCategoryId1]);

        return cy.api_patch(`/contacts/${contactId}`, {
          secondary_categories: [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ],
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ]);

        return cy.api_get(`/contacts/${contactId}`);
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ]);

        // Test that patching only the name leaves categories intact
        return cy.api_patch(`/contacts/${contactId}`, {
          name: 'updated name',
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [
            secondaryCategoryId1,
            secondaryCategoryId2,
          ]);

        // Remove one category
        return cy.api_patch(`/contacts/${contactId}`, {
          secondary_categories: [secondaryCategoryId2],
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', [secondaryCategoryId2]);

        // Remove all secondary categories
        return cy.api_patch(`/contacts/${contactId}`, {
          secondary_categories: [],
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', []);

        // Patch name again to ensure empty categories are preserved
        return cy.api_patch(`/contacts/${contactId}`, {
          name: 'updated again',
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.secondary_categories')
          .should('deep.equal', []);
      });
  });

  it('returns custom fields assigned to secondary categories', () => {
    let primaryCategoryId = 0;
    let secondaryCategoryId = 0;
    let fieldId = 0;
    let contactId = 0;

    cy.db_createCategory({ extension: 'com_contact', title: 'Primary Field Cat' })
      .then((categoryId) => {
        primaryCategoryId = categoryId;
        return cy.db_createCategory({ extension: 'com_contact', title: 'Secondary Field Cat' });
      })
      .then((categoryId) => {
        secondaryCategoryId = categoryId;

        return cy.db_createField({
          title: 'test field',
          name: 'test-secondary-field',
          type: 'text',
          context: 'com_contact.contact',
          state: 1,
          access: 1,
        });
      })
      .then((createdFieldId) => {
        fieldId = createdFieldId;

        return cy.task('queryDB', `INSERT INTO #__fields_categories (field_id, category_id) VALUES (${fieldId}, ${secondaryCategoryId})`);
      })
      .then(() => {
        return cy.api_post('/contacts', {
          name: 'Contact with secondary fields',
          alias: 'contact-secondary-fields',
          catid: primaryCategoryId,
          secondary_categories: [secondaryCategoryId],
          published: 1,
          language: '*',
          com_fields: {
            'test-secondary-field': 'This is field data!',
          },
        });
      })
      .then((response) => {
        contactId = response.body.data.id;
        cy.wrap(response).its('body.data.attributes')
          .should('have.property', 'test-secondary-field', 'This is field data!');

        return cy.api_get(`/contacts/${contactId}`);
      })
      .then((response) => {
        cy.wrap(response).its('body.data.attributes')
          .should('have.property', 'test-secondary-field', 'This is field data!');
      });
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
      .then((contact) => cy.api_delete(`/contacts/${contact.id}`))
      .then((result) => expect(result.status).to.eq(204));
  });

  it('check correct response for delete a not existent contact', () => {
    cy.api_getBearerToken().then((token) => {
      cy.request({
        method: 'DELETE',
        url: '/api/index.php/v1/contacts/9999',
        headers: {
          Authorization: `Bearer ${token}`,
        },
        failOnStatusCode: false,
      }).then((response) => {
        expect(response.status).to.equal(404);
        expect(response.body.data.message).to.include('Resource not found');
      });
    });
  });

  it('preserves existing tags when updating via patch without sending tags', () => {
    let contactId = 0;
    let tagId = 0;

    cy.db_createTag({ title: 'automated test tag' })
      .then((createdTagId) => {
        tagId = createdTagId;
        return cy.db_createCategory({ extension: 'com_contact' });
      })
      .then((categoryId) => {
        return cy.api_post('/contacts', {
          name: 'contact with initial tag',
          alias: 'contact-initial-tag',
          catid: categoryId,
          published: 1,
          language: '*',
          tags: [tagId],
        });
      })
      .then((response) => {
        contactId = response.body.data.id;

        cy.wrap(response)
          .its('body.data.attributes.tags')
          .should('have.property', String(tagId), 'automated test tag');

        return cy.api_patch(`/contacts/${contactId}`, {
          name: 'updated contact name without tags field',
        });
      })
      .then((response) => {
        cy.wrap(response)
          .its('body.data.attributes.tags')
          .should('have.property', String(tagId), 'automated test tag');
      });
  });

  it('can submit a contact form', () => {
    cy.db_getUserId().then((id) => cy.db_createContact({ name: 'automated test contact', user_id: id, params: '{"show_email_form":"1"}' }))
      .then((contact) => cy.api_post(`/contacts/form/${contact.id}`, {
        contact_name: Cypress.expose('name'),
        contact_email: Cypress.expose('email'),
        contact_subject: 'automated test subject',
        contact_message: 'automated test message',
      }))
      .then((response) => cy.wrap(response).its('status')
        .should('equal', 200));

    cy.task('getMails').then((mails) => {
      expect(mails.length).to.equal(1);
      cy.wrap(mails[0].sender).should('equal', Cypress.expose('email'));
      cy.wrap(mails[0].receivers).should('have.property', Cypress.expose('email'));
      cy.wrap(mails[0].headers.subject).should('equal', `${Cypress.expose('sitename')}: automated test subject`);
      cy.wrap(mails[0].body).should('have.string', 'This is an enquiry email via');
      cy.wrap(mails[0].body).should('have.string', `${Cypress.expose('name')} ${Cypress.expose('email')}`);
      cy.wrap(mails[0].body).should('have.string', 'automated test message');
    });
  });
});
