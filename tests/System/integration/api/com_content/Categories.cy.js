import { AST_CATEGORY_TITLE } from '../../../support/constants.mjs';

describe('Test that content categories API endpoint', () => {
  beforeEach(() => cy.task('queryDB', `DELETE FROM #__categories WHERE title LIKE '${AST_CATEGORY_TITLE}%'`));

  it('can deliver a list of categories', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} 1`, extension: 'com_content', published: 1 })
      .then((res) => cy.db_createArticle({ title: 'automated test article', catid: Number(res.body.data.id) }))
      .then(() => cy.api_get('/content/categories'))
      .then((response) => cy.api_responseContains(response, 'title', `${AST_CATEGORY_TITLE} 1`));
  });

  it('can deliver a single category', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} 2`, extension: 'com_content', published: 1 })
      .then((res) => cy.api_get(`${'/content/categories'}/${Number(res.body.data.id)}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes').its('title')
        .should('include', `${AST_CATEGORY_TITLE} 2`));
  });

  it('can create a category', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} 3`, description: 'automated test content category description', parent_id: 1, extension: 'com_content' })
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes').its('title').should('include', `${AST_CATEGORY_TITLE} 3`);
        cy.wrap(response).its('body').its('data').its('attributes').its('description').should('include', 'automated test content category description');
      });
  });

  it('can update a category', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} 4`, extension: 'com_content', published: 1 })
      .then((res) => cy.api_patch(`${'/content/categories'}/${Number(res.body.data.id)}`, {
        title: `${AST_CATEGORY_TITLE} 4 Updated`,
        description: 'automated test content category description' }))
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', `${AST_CATEGORY_TITLE} 4 Updated`);
        cy.wrap(response).its('body')
          .its('data').its('attributes').its('description')
          .should('include', 'automated test content category description');
      });
  });
});
