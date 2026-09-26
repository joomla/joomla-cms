describe('Test that the task notification system plugin', () => {
  beforeEach(() => {
    cy.task('clearEmails');
    cy.doAdministratorLogin();
  });

  it('can display notification form', () => {
    cy.visit('/administrator/index.php?option=com_scheduler&view=tasks');
    cy.clickToolbarButton('New');
    cy.get('div.new-task-details').contains('Delete ActionLogs').click();
    cy.title().should('contain', 'New Task');
    cy.get('h1.page-title').should('contain', 'New Task');
    cy.get('#myTab div[role="tablist"] button[aria-controls="advanced"]').click();
    cy.get('#task-form').contains('Notification').should('be.visible');
  });

  it('can notify successful task execution', () => {
    cy.db_createSchedulerTask({
      title: 'Test task',
      type: 'delete.actionlogs',
      execution_rules: { 'rule-type': 'manual' },
      cron_rules: { type: 'manual', exp: '' },
      params: {
        notifications: { success_mail: 1 },
        logDeletePeriod: 7,
      },
    }).then((task) => {
      cy.visit('/administrator/index.php?option=com_scheduler&view=tasks&filter=');
      cy.searchForItem('Test task');
      cy.intercept('GET', '**/administrator/index.php?option=com_ajax&format=json&plugin=RunSchedulerTest&group=system&id=*').as('runschedulertest');
      cy.get('button[data-scheduler-run]').should('have.attr', 'data-id', task.id).click();
      cy.wait('@runschedulertest').then((interception) => {
        expect(interception.response.body.message).to.eq(null);
        expect(interception.response.body.success).to.eq(true);
      });
      cy.get('joomla-dialog[type="inline"]').should('be.visible');
      cy.get('joomla-dialog[type="inline"]').within(() => {
        cy.get('header.joomla-dialog-header').should('contain', `Test task (ID: ${task.id})`);
        cy.get('div.scheduler-status').should('contain', 'Status: Completed');
      });
      cy.task('getMails').then((mails) => {
        cy.wrap(mails).should('have.lengthOf', 1);
        cy.wrap(mails[0].body).should('have.string', `Scheduled Task#${task.id}, Test task, has been successfully executed`);
        cy.wrap(mails[0].headers.subject).should('have.string', 'Task Successful');
        cy.wrap(mails[0].headers.from).should('equal', `"${Cypress.env('sitename')}" <${Cypress.env('email')}>`);
        cy.wrap(mails[0].headers.to).should('equal', Cypress.env('email'));
      });
    });
  });

  it('can notify failed task execution', () => {
    cy.db_createSchedulerTask({
      title: 'Test task',
      type: 'plg_task_requests_task_get',
      execution_rules: { 'rule-type': 'manual' },
      cron_rules: { type: 'manual', exp: '' },
      params: {
        notifications: { failure_mail: 1 },
        url: `${Cypress.config('baseUrl').replace('https://', 'http://')}/invalid.html`,
        timeout: 120,
      },
    }).then((task) => {
      cy.visit('/administrator/index.php?option=com_scheduler&view=tasks&filter=');
      cy.searchForItem('Test task');
      cy.intercept('GET', '**/administrator/index.php?option=com_ajax&format=json&plugin=RunSchedulerTest&group=system&id=*').as('runschedulertest');
      cy.get('button[data-scheduler-run]').should('have.attr', 'data-id', task.id).click();
      cy.wait('@runschedulertest').then((interception) => {
        expect(interception.response.body.message).to.eq(null);
        expect(interception.response.body.success).to.eq(true);
      });
      cy.get('joomla-dialog[type="inline"]').should('be.visible');
      cy.get('joomla-dialog[type="inline"]').within(() => {
        cy.get('header.joomla-dialog-header').should('contain', `Test task (ID: ${task.id})`);
        cy.get('div.scheduler-status').should('contain', 'Status: Completed');
      });
      cy.task('getMails').then((mails) => {
        cy.wrap(mails).should('have.lengthOf', 1);
        cy.wrap(mails[0].body).should('have.string', `Scheduled Task#${task.id}, Test task, has failed with exit code 5`);
        cy.wrap(mails[0].attachments).should('have.lengthOf', 1);
        cy.wrap(mails[0].headers.subject).should('have.string', 'Task Failure');
        cy.wrap(mails[0].headers.from).should('equal', `"${Cypress.env('sitename')}" <${Cypress.env('email')}>`);
        cy.wrap(mails[0].headers.to).should('equal', Cypress.env('email'));
      });
    });
  });
});
