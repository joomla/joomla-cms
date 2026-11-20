describe('Test in backend that the tasks list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_scheduler&view=tasks&filter=');
  });
  afterEach(() => cy.task('queryDB', "DELETE FROM #__scheduler_tasks WHERE title = 'Test task'"));

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', ' Scheduled Tasks');
  });

  it('can display a list of tasks', () => {
    cy.contains('Update Notification');
  });

  it('can open the task list', () => {
    cy.clickToolbarButton('New');
    cy.contains('Select a Task type');
    cy.contains('GET Request');
  });

  it('can publish the test task', () => {
    cy.clickToolbarButton('New');
    cy.get('#comSchedulerSelectSearch').clear().type('GET');
    cy.get('a[href*="plg_task_requests_task_get"]').click();
    cy.get('#jform_title').clear().type('Test task');
    cy.get('#jform_params_url').clear().type('www.test.task');
    cy.get('#jform_execution_rules_interval_minutes').clear().type('1');
    cy.clickToolbarButton('Save & Close');
    cy.checkForSystemMessage('Item saved');
  });

  it('can unpublish the test task', () => {
    cy.clickToolbarButton('New');
    cy.get('#comSchedulerSelectSearch').clear().type('GET');
    cy.get('a[href*="plg_task_requests_task_get"]').click();
    cy.get('#jform_title').clear().type('Test task');
    cy.get('#jform_params_url').clear().type('www.test.task');
    cy.get('#jform_execution_rules_interval_minutes').clear().type('1');
    cy.clickToolbarButton('Save & Close');
    cy.checkForSystemMessage('Item saved');

    cy.reload();
    cy.searchForItem('Test task');
    cy.checkAllResults();
    cy.clickToolbarButton('Action');
    cy.contains('Disable').click();
    cy.on('window:confirm', () => true);

    cy.checkForSystemMessage('Task disabled');
  });

  it('can trash the test task', () => {
    cy.clickToolbarButton('New');
    cy.get('#comSchedulerSelectSearch').clear().type('GET');
    cy.get('a[href*="plg_task_requests_task_get"]').click();
    cy.get('#jform_title').clear().type('Test task');
    cy.get('#jform_params_url').clear().type('www.test.task');
    cy.get('#jform_execution_rules_interval_minutes').clear().type('1');
    cy.get('#jform_state').select('Disabled');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Item saved');

    cy.reload();
    cy.searchForItem('Test task');
    cy.checkAllResults();
    cy.clickToolbarButton('Action');
    cy.contains('Trash').click();
    cy.on('window:confirm', () => true);

    cy.checkForSystemMessage('Task trashed');
  });

  it('can delete the test task', () => {
    cy.clickToolbarButton('New');
    cy.get('#comSchedulerSelectSearch').clear().type('GET');
    cy.get('a[href*="plg_task_requests_task_get"]').click();
    cy.get('#jform_title').clear().type('Test task');
    cy.get('#jform_params_url').clear().type('www.test.task');
    cy.get('#jform_execution_rules_interval_minutes').clear().type('1');
    cy.get('#jform_state').select('Trashed');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Item saved');

    cy.reload();

    cy.setFilter('state', 'Trashed');
    cy.searchForItem('Test task');
    cy.checkAllResults();
    cy.clickToolbarButton('empty trash');
    cy.clickDialogConfirm(true);

    cy.checkForSystemMessage('Task deleted');
  });
});
