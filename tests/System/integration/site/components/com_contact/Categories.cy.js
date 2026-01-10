import { AST_CATEGORY_TITLE } from '../../../../support/constants.mjs';

describe('Test in frontend that the contact categories view', () => {
  beforeEach(() => {
    cy.task('queryDB', `DELETE FROM #__categories WHERE title LIKE '${AST_CATEGORY_TITLE}%'`);
  });

  it('can display a list of contact categories without a menu item', () => {
    cy.api_post('/contacts/categories', { title: `${AST_CATEGORY_TITLE} 1`, extension: 'com_contacts', parent_id: 1, published: 1 })
      .then((res) => {
        const catId = Number(res.body.data.id);
        return cy.db_createContact({ name: 'automated test contact 1', catid: catId });
      })
      .then(() => cy.api_post('/contacts/categories', { title: `${AST_CATEGORY_TITLE} 2`, extension: 'com_contacts', parent_id: 1, published: 1 }))
      .then(async (res) => {
        const catId = Number(res.body.data.id);
        await cy.db_createContact({ name: 'automated test contact 2', catid: catId });
        await cy.db_createContact({ name: 'automated test contact 3', catid: catId });
      })
      .then(() => {
        cy.visit('/index.php?option=com_contact&view=categories');

        cy.contains(`${AST_CATEGORY_TITLE} 1`);
        cy.contains(`${AST_CATEGORY_TITLE} 2`);
        cy.get(':nth-child(2) > .page-header > .badge').contains('Contact Count: 1');
        cy.get(':nth-child(3) > .page-header > .badge').contains('Contact Count: 2');
      });
  });

  it('can display a list of categories in a menu item', () => {
    cy.api_post('/contacts/categories', { title: `${AST_CATEGORY_TITLE} 1`, extension: 'com_contacts', parent_id: 1, published: 1 })
      .then((res) => {
        const catId = Number(res.body.data.id);
        return cy.db_createContact({ name: 'automated test contact 1', catid: catId });
      })
      .then(() => cy.api_post('/contacts/categories', { title: `${AST_CATEGORY_TITLE} 2`, extension: 'com_contacts', parent_id: 1, published: 1 }))
      .then(async (res) => {
        const catId = Number(res.body.data.id);
        await cy.db_createContact({ name: 'automated test contact 2', catid: catId });
        await cy.db_createContact({ name: 'automated test contact 3', catid: catId });
      })
      .then(() => cy.db_createMenuItem({ title: 'automated test categories', link: 'index.php?option=com_contact&view=categories' }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test categories)').click();

        cy.contains(`${AST_CATEGORY_TITLE} 1`);
        cy.contains(`${AST_CATEGORY_TITLE} 2`);
        cy.get(':nth-child(1) > .page-header > .badge').contains('Contact Count: 1');
        cy.get(':nth-child(2) > .page-header > .badge').contains('Contact Count: 2');
      });
  });
});
