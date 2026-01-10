import { AST_CATEGORY_TITLE } from '../../../../support/constants.mjs';

describe('Test in frontend that the content categories view', () => {
  beforeEach(() => {
    cy.task('queryDB', `DELETE FROM #__categories WHERE title LIKE '${AST_CATEGORY_TITLE}%'`);
  });

  it('can display a list of categories without a menu item', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} 1`, extension: 'com_content', published: 1 })
      .then((res) => {
        const catId = Number(res.body.data.id);
        return cy.db_createArticle({ title: 'automated test article 1', catid: catId });
      })
      .then(() => cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} 2`, extension: 'com_content', published: 1 }))
      .then((res) => {
        const catId = Number(res.body.data.id);
        return Promise.all([
          cy.db_createArticle({ title: 'automated test article 2', catid: catId }),
          cy.db_createArticle({ title: 'automated test article 3', catid: catId }),
        ]);
      })
      .then(() => {
        cy.visit('/index.php?option=com_content&view=categories');

        cy.contains(`${AST_CATEGORY_TITLE} 1`);
        cy.get(':nth-child(1) > .com-content-categories__item-title-wrapper > .com-content-categories__item-title > .badge').contains('Article Count: 1');
        cy.contains(`${AST_CATEGORY_TITLE} 2`);
        cy.get(':nth-child(2) > .com-content-categories__item-title-wrapper > .com-content-categories__item-title > .badge').contains('Article Count: 2');
      });
  });

  it('can display a list of categories in a menu item', () => {
    cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} 1`, extension: 'com_content', published: 1 })
      .then((res) => {
        const catId = Number(res.body.data.id);
        return cy.db_createArticle({ title: 'automated test article 1', catid: catId });
      })
      .then(() => cy.api_post('/content/categories', { title: `${AST_CATEGORY_TITLE} 2`, extension: 'com_content', published: 1 }))
      .then((res) => {
        const catId = Number(res.body.data.id);
        return Promise.all([
          cy.db_createArticle({ title: 'automated test article 2', catid: catId }),
          cy.db_createArticle({ title: 'automated test article 3', catid: catId }),
        ]);
      })
      .then(() => cy.db_createMenuItem({ title: 'automated test categories', link: 'index.php?option=com_content&view=categories' }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test categories)').click();

        cy.contains(`${AST_CATEGORY_TITLE} 1`);
        cy.get(':nth-child(1) > .com-content-categories__item-title-wrapper > .com-content-categories__item-title > .badge').contains('Article Count: 1');
        cy.contains(`${AST_CATEGORY_TITLE} 2`);
        cy.get(':nth-child(2) > .com-content-categories__item-title-wrapper > .com-content-categories__item-title > .badge').contains('Article Count: 2');
      });
  });
});
