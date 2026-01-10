import { AST_CATEGORY_TITLE } from '../../../../support/constants.mjs';

beforeEach(() => cy.task('queryDB', `DELETE FROM #__categories WHERE title LIKE '${AST_CATEGORY_TITLE}%'`));

describe('Test in frontend that the newsfeeds categories view', () => {
  it('can display a list of categories', () => {
    cy.api_post('/newsfeeds/categories', { title: `${AST_CATEGORY_TITLE} 1`, parent_id: 1, extension: 'com_newsfeeds', published: 1 })
      .then((res) => {
        const cat1Id = Number(res.body.data.id);
        return cy.db_createNewsFeed({ name: 'automated test feed 1', catid: cat1Id });
      })
      .then(() => cy.api_post('/newsfeeds/categories', { title: `${AST_CATEGORY_TITLE} 2`, parent_id: 1, extension: 'com_newsfeeds', published: 1 }))
      .then((res) => {
        const cat2Id = Number(res.body.data.id);
        return cy.db_createNewsFeed({ name: 'automated test feed 2', catid: cat2Id })
          .then(() => cy.db_createNewsFeed({ name: 'automated test feed 3', catid: cat2Id }));
      })
      .then(() => {
        cy.visit('/index.php?option=com_newsfeeds&view=categories');

        cy.contains(`${AST_CATEGORY_TITLE} 1`);
        cy.contains(`${AST_CATEGORY_TITLE} 2`);
        cy.get(':nth-child(2) > .page-header > .badge').contains('# News feeds 1');
        cy.get(':nth-child(3) > .page-header > .badge').contains('# News feeds 2');
      });
  });
});
