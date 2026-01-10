import { AST_CATEGORY_TITLE } from '../../../../support/constants.mjs';

describe('Test in frontend that the articles categories module', () => {
  beforeEach(() => {
    cy.task('queryDB', `DELETE FROM #__categories WHERE title LIKE '${AST_CATEGORY_TITLE}%'`);
  });

  it('can display the title of the categories', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} Categories`, extension: 'com_content', parent_id: 1, published: 1 })
      .then(() => cy.db_createModule({ module: 'mod_articles_categories' }))
      .then(() => {
        cy.visit('/');

        cy.contains('li', `${AST_CATEGORY_TITLE} Categories`);
      });
  });
});
