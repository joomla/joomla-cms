describe('Test that the content and contact API endpoints check the permissions of the category', () => {
  const denied = JSON.stringify({
    'core.create': { 6: 0 }, 'core.edit': { 6: 0 }, 'core.edit.own': { 6: 0 }, 'core.edit.state': { 6: 0 },
  });

  let manager;
  let allowedCategory;
  let deniedCategory;
  let article;
  let contactAllowedCategory;
  let contactDeniedCategory;
  let contact;
  const rules = {};

  const articleData = (catid, title) => ({
    title, alias: title.replace(/ /g, '-'), catid, introtext: 'test', state: 1, access: 1, language: '*',
  });

  const contactData = (catid, name) => ({
    name, alias: name.replace(/ /g, '-'), catid, published: 1, language: '*',
  });

  before(() => {
    // The manager group is not allowed to use the content and contact components in the API by default
    ['com_content', 'com_contact'].forEach((name) => cy.api_setAssetRules(name, { 'core.api': { 6: 1 } }).then((previous) => {
      rules[name] = previous;
    }));

    // Two categories, the manager is not allowed to create or edit in the second one
    cy.api_post('/content/categories', { title: 'ci acl allowed', parent_id: 1, extension: 'com_content' })
      .then((response) => {
        allowedCategory = Number(response.body.data.id);
      });
    cy.api_post('/content/categories', { title: 'ci acl denied', parent_id: 1, extension: 'com_content' })
      .then((response) => {
        deniedCategory = Number(response.body.data.id);

        return cy.task('queryDB', `UPDATE #__assets SET rules = '${denied}' WHERE name = 'com_content.category.${deniedCategory}'`);
      });
    cy.api_post('/contacts/categories', { title: 'ci acl contact allowed', parent_id: 1, extension: 'com_contact' })
      .then((response) => {
        contactAllowedCategory = Number(response.body.data.id);
      });
    cy.api_post('/contacts/categories', { title: 'ci acl contact denied', parent_id: 1, extension: 'com_contact' })
      .then((response) => {
        contactDeniedCategory = Number(response.body.data.id);

        return cy.task('queryDB', `UPDATE #__assets SET rules = '${denied}' WHERE name = 'com_contact.category.${contactDeniedCategory}'`);
      });
    cy.then(() => cy.api_post('/contacts', contactData(contactDeniedCategory, 'ci acl contact')))
      .then((response) => {
        contact = Number(response.body.data.id);
      });
    cy.then(() => cy.api_post('/content/articles', articleData(allowedCategory, 'ci acl article')))
      .then((response) => {
        article = Number(response.body.data.id);
      });
  });

  // The database cleanup after each test removes the user, so it is created for each test
  beforeEach(() => {
    cy.db_createUser({
      group_id: 6, username: 'apimanager788', name: 'apimanager788', email: 'apimanager788@example.com',
    }).then((userId) => cy.api_getBearerToken(userId)).then((token) => {
      manager = { token };
    });
  });

  after(() => {
    Object.entries(rules).forEach(([name, previous]) => cy.task('queryDB', `UPDATE #__assets SET rules = '${previous}' WHERE name = '${name}'`));
    cy.task('queryDB', 'DELETE FROM #__content WHERE title LIKE \'ci acl %\'');
    cy.task('queryDB', 'DELETE FROM #__contact_details WHERE name LIKE \'ci acl %\'');
    cy.task('queryDB', `DELETE FROM #__categories WHERE id IN (${contactAllowedCategory}, ${contactDeniedCategory})`);
    cy.task('queryDB', 'DELETE FROM #__assets WHERE title LIKE \'ci acl%\'');
    cy.task('queryDB', `DELETE FROM #__categories WHERE id IN (${allowedCategory}, ${deniedCategory})`);
  });

  it('allows to create an article in a category with the permission', () => {
    cy.api_requestAs(manager.token, 'POST', '/content/articles', articleData(allowedCategory, 'ci acl allowed'))
      .its('status').should('eq', 200);
  });

  it('does not allow to create an article in a category without the permission', () => {
    cy.api_requestAs(manager.token, 'POST', '/content/articles', articleData(deniedCategory, 'ci acl denied'))
      .its('status').should('eq', 403);
    cy.task('queryDB', 'SELECT id FROM #__content WHERE title = \'ci acl denied\'').should('have.length', 0);
  });

  it('allows to edit an article within a category with the permission', () => {
    cy.api_requestAs(manager.token, 'PATCH', `/content/articles/${article}`, { title: 'ci acl edited' })
      .its('status').should('eq', 200);
    cy.api_requestAs(manager.token, 'PATCH', `/content/articles/${article}`, { catid: allowedCategory })
      .its('status').should('eq', 200);
  });

  it('does not allow to create an article in a category without the permission with the id in exponent notation', () => {
    cy.api_requestAs(manager.token, 'POST', '/content/articles', articleData(`${deniedCategory / 1000}e3`, 'ci acl denied exp'))
      .its('status').should('eq', 403);
    cy.task('queryDB', 'SELECT id FROM #__content WHERE title = \'ci acl denied exp\'').should('have.length', 0);
  });

  it('does not allow to create an article in a category without the permission with trailing characters after the id', () => {
    cy.api_requestAs(manager.token, 'POST', '/content/articles', articleData(`${deniedCategory}abc`, 'ci acl denied suffix'))
      .its('status').should('eq', 403);
    cy.task('queryDB', 'SELECT id FROM #__content WHERE title = \'ci acl denied suffix\'').should('have.length', 0);
  });

  it('does not allow to move an article into a category without the permission', () => {
    cy.api_requestAs(manager.token, 'PATCH', `/content/articles/${article}`, { catid: deniedCategory })
      .its('status').should('eq', 403);
    cy.task('queryDB', `SELECT catid FROM #__content WHERE id = ${article}`)
      .then((rows) => expect(rows[0].catid).to.eq(allowedCategory));
  });

  it('does not allow to edit a contact within a category without the permission', () => {
    cy.api_requestAs(manager.token, 'PATCH', `/contacts/${contact}`, { name: 'ci acl contact edited' })
      .its('status').should('eq', 403);
    cy.task('queryDB', `SELECT name FROM #__contact_details WHERE id = ${contact}`)
      .then((rows) => expect(rows[0].name).to.eq('ci acl contact'));
  });

  it('does not allow to create a contact in a category without the permission', () => {
    cy.api_requestAs(manager.token, 'POST', '/contacts', contactData(contactDeniedCategory, 'ci acl contact new'))
      .its('status').should('eq', 403);
  });

  it('allows to create and edit a contact in a category with the permission', () => {
    cy.api_requestAs(manager.token, 'POST', '/contacts', contactData(contactAllowedCategory, 'ci acl contact allowed'))
      .then((response) => {
        expect(response.status).to.eq(200);

        return cy.api_requestAs(manager.token, 'PATCH', `/contacts/${response.body.data.id}`, { name: 'ci acl contact allowed 2' });
      })
      .its('status').should('eq', 200);
  });
});
