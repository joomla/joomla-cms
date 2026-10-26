describe('Test that the notification workflow plugin', () => {
  before(() => {
    cy.db_updateExtensionParameter('workflow_enabled', '1', 'com_content');
    cy.task('writeRelativeFile', {
      path: 'administrator/language/overrides/en-GB.override.ini',
      content: 'WORKFLOW_NOTIFICATION="Workflow notification"\nSTAGE_NOTIFICATION="Stage notification"\nTRANSITION_NOTIFICATION_1="Transition notification 1"\nTRANSITION_NOTIFICATION_2="Transition notification 2"\n',
    });
  });
  beforeEach(() => {
    cy.task('clearEmails');
    cy.doAdministratorLogin();
  });
  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__workflow_associations WHERE item_id IN (SELECT id FROM #__content WHERE title = 'Article notification') AND extension = 'com_content.article'");
    cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Article notification'");
    cy.task('queryDB', 'UPDATE #__workflows SET `default` = 1 WHERE id = 1');
  });
  after(() => {
    cy.db_updateExtensionParameter('workflow_enabled', '0', 'com_content');
    cy.task('deleteRelativePath', 'administrator/language/overrides/en-GB.override.ini');
  });

  it('can modify transition edit form', () => {
    cy.db_createWorkflowTransition({ title: 'Test transition' }).then((transition) => {
      cy.visit(`/administrator/index.php?option=com_workflow&task=transition.edit&workflow_id=1&extension=com_content.article&id=${transition.id}`);
      cy.get('#myTab div[role="tablist"] button[aria-controls="attrib-notification"]').click();
      cy.get('#jform_options_notification_send_mail-lbl').should('contain', 'Send Notification');
      cy.get('#jform_options_notification_send_mail').should('exist');
      cy.selectOptionInFancySelect('#jform_options_notification_groups', 'Super Users');
      cy.clickToolbarButton('Save');
      cy.checkForSystemMessage('Item saved.');
      cy.get('#jform_options_notification_groups').find(':selected').should('contain', 'Super Users');
    });
  });

  it('can send notification', () => {
    cy.db_createWorkflow({ title: 'WORKFLOW_NOTIFICATION', extension: 'com_content.article', default: 1 }).then((workflow) => {
      cy.db_createWorkflowStage({ title: 'STAGE_NOTIFICATION', workflow_id: workflow.id, default: 1 }).then((stage) => {
        cy.db_createUser({
          name: 'Test user 1',
          username: 'user1',
          email: 'user1@example.com',
          group_id: 8,
        }).then(() => {
          cy.db_createWorkflowTransition({
            title: 'TRANSITION_NOTIFICATION_1',
            workflow_id: workflow.id,
            to_stage_id: stage.id,
            options: { notification_send_mail: 1, notification_groups: [8] },
          });
        });
        cy.db_createUser({
          name: 'Test user 2',
          username: 'user2',
          email: 'user2@example.com',
          group_id: 7,
        }).then((user) => {
          cy.db_createWorkflowTransition({
            title: 'TRANSITION_NOTIFICATION_2',
            workflow_id: workflow.id,
            to_stage_id: stage.id,
            options: { notification_send_mail: 1, notification_text: 'extra notification', notification_receivers: [user] },
          });
        });
      });
    });
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Article notification');
    cy.get('#jform_transition').find(':selected').should('contain', 'Stage notification');
    cy.get('#jform_transition').select('Transition notification 1');
    cy.clickToolbarButton('Save');
    cy.checkForSystemMessage('Article saved.');
    cy.checkForSystemMessage('Notifications sent');
    cy.task('getMails').then((mails) => {
      cy.wrap(mails).should('have.lengthOf', 1);
      cy.wrap(mails[0].body).should('have.string', `Title: Article notification. Transition "Transition notification 1" performed by ${Cypress.env('name')}. New state: Stage notification.`);
      cy.wrap(mails[0].headers.subject).should('have.string', `[${Cypress.env('sitename')}] - The status of "Article notification" has been changed`);
      cy.wrap(mails[0].headers.from).should('equal', `"${Cypress.env('sitename')}" <${Cypress.env('email')}>`);
      cy.wrap(mails[0].headers.to).should('equal', '"Test user 1" <user1@example.com>');
    });
    cy.task('clearEmails');
    cy.get('#jform_transition').select('Transition notification 2');
    cy.clickToolbarButton('Save');
    cy.checkForSystemMessage('Article saved.');
    cy.checkForSystemMessage('Notifications sent');
    cy.task('getMails').then((mails) => {
      cy.wrap(mails).should('have.lengthOf', 1);
      cy.wrap(mails[0].body).should('have.string', `Title: Article notification. Transition "Transition notification 2" performed by ${Cypress.env('name')}. New state: Stage notification.extra notification`);
      cy.wrap(mails[0].headers.subject).should('have.string', `[${Cypress.env('sitename')}] - The status of "Article notification" has been changed`);
      cy.wrap(mails[0].headers.from).should('equal', `"${Cypress.env('sitename')}" <${Cypress.env('email')}>`);
      cy.wrap(mails[0].headers.to).should('equal', '"Test user 2" <user2@example.com>');
    });
  });
});
