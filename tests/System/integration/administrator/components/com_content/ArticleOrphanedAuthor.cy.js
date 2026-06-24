describe('Test in backend that article save auto-assigns author when created_by is orphaned', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
  });

  afterEach(() => cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Test orphan article'"));

  it('assigns current user when created_by is 0 (author already missing)', () => {
    cy.db_getUserId().then((adminId) => {
      cy.db_createArticle({ title: 'Test orphan article', created_by: 0 }).then((article) => {
        cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
        cy.clickToolbarButton('Save & Close');

        cy.checkForSystemMessage('Article saved.');

        cy.task('queryDB', `SELECT created_by FROM #__content WHERE id = ${article.id}`).then((rows) => {
          expect(rows[0].created_by).to.equal(adminId);
        });
      });
    });
  });

  it('assigns current user when created_by references a deleted user', () => {
    cy.db_getUserId().then((adminId) => {
      // Create a temporary user
      cy.db_createUser({ name: 'Temp Author', username: 'tempauthor', email: 'tempauthor@example.com' }).then((tempUserId) => {
        // Create article authored by the temp user
        cy.db_createArticle({ title: 'Test orphan article', created_by: tempUserId }).then((article) => {
          // Delete the temp user to orphan the article
          cy.task('queryDB', `DELETE FROM #__user_usergroup_map WHERE user_id = ${tempUserId}`);
          cy.task('queryDB', `DELETE FROM #__users WHERE id = ${tempUserId}`);

          // Open and save the article as admin
          cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
          cy.clickToolbarButton('Save & Close');

          cy.checkForSystemMessage('Article saved.');

          cy.task('queryDB', `SELECT created_by FROM #__content WHERE id = ${article.id}`).then((rows) => {
            expect(rows[0].created_by).to.equal(adminId);
          });
        });
      });
    });
  });

  it('preserves created_by when author is a valid existing user', () => {
    cy.db_createUser({ name: 'Valid Author', username: 'validauthor', email: 'validauthor@example.com' }).then((validUserId) => {
      cy.db_createArticle({ title: 'Test orphan article', created_by: validUserId }).then((article) => {
        cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
        cy.clickToolbarButton('Save & Close');

        cy.checkForSystemMessage('Article saved.');

        cy.task('queryDB', `SELECT created_by FROM #__content WHERE id = ${article.id}`).then((rows) => {
          expect(rows[0].created_by).to.equal(validUserId);
        });
      });

      // Cleanup
      cy.task('queryDB', `DELETE FROM #__user_usergroup_map WHERE user_id = ${validUserId}`);
      cy.task('queryDB', `DELETE FROM #__users WHERE id = ${validUserId}`);
    });
  });
});
