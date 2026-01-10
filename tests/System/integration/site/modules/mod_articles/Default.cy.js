import { AST_CATEGORY_TITLE } from '../../../../support/constants.mjs';

describe('Test in frontend that the articles module', () => {
  beforeEach(() => {
    cy.task('queryDB', `DELETE FROM #__categories WHERE title LIKE '${AST_CATEGORY_TITLE}%'`);
  });

  it('can display the title of the article', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} Articles`, extension: 'com_content', parent_id: 1, published: 1 })
      .then(async (catRes) => {
        const categoryId = Number(catRes.body.data.id);
        await cy.db_createArticle({ title: 'automated test article', catid: categoryId });
        await cy.db_createModule({
          module: 'mod_articles',
          params: JSON.stringify({
            catid: categoryId,
            item_title: 1,
            show_introtext: 0,
          }),
        });
      })
      .then(() => {
        cy.visit('/');

        cy.contains('li', 'automated test article');
      });
  });
});
