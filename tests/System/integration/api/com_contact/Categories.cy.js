import { AST_CATEGORY_TITLE } from '../../../support/constants.mjs';

describe('Test that contact categories API endpoint', () => {
  beforeEach(() => cy.task('queryDB', `DELETE FROM #__categories WHERE title LIKE '${AST_CATEGORY_TITLE}%'`));

  it('can deliver a list of categories', () => {
    cy.api_post('/contacts/categories', { title: `${AST_CATEGORY_TITLE} 1`, extension: 'com_contacts', parent_id: 1, published: 1 })
      .then((res) => {
        const catId = Number(res.body.data.id);
        return cy.db_createContact({ name: 'automated test contact', catid: catId });
      })
      .then(() => cy.api_get('/contacts/categories'))
      .then((response) => cy.api_responseContains(response, 'title', `${AST_CATEGORY_TITLE} 1`));
  });

  it('can deliver a single category', () => {
    cy.api_post('/contacts/categories', { title: `${AST_CATEGORY_TITLE} 2`, extension: 'com_contacts', parent_id: 1, published: 1 })
      .then((res) => {
        const catId = Number(res.body.data.id);
        return cy.api_get(`/contacts/categories/${catId}`);
      })
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', `${AST_CATEGORY_TITLE} 2`));
  });

  it('can create a category', () => {
    cy.api_post('/contacts/categories', {
      title: `${AST_CATEGORY_TITLE} 3`,
      description: 'automated test contact category description',
      parent_id: 1,
      extension: 'com_contacts',
    })
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `${AST_CATEGORY_TITLE} 3`);
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('description')
          .should('include', 'automated test contact category description');
      });
  });

  it('can update a category', () => {
    cy.api_post('/contacts/categories', { title: `${AST_CATEGORY_TITLE} 4`, extension: 'com_contacts', parent_id: 1, published: 1 })
      .then((res) => {
        const catId = Number(res.body.data.id);
        return cy.api_patch(`/contacts/categories/${catId}`, { title: `${AST_CATEGORY_TITLE} 4 Updated`, description: 'automated test contact category description' });
      })
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `${AST_CATEGORY_TITLE} 4 Updated`);
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('description')
          .should('include', 'automated test contact category description');
      });
  });
});
