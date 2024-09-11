/**
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * Provides the manual-run functionality for tasks over the com_scheduler administrator backend.
 *
 * @package  Joomla.Components
 * @subpackage Scheduler.Tasks
 *
 * @since    4.1.0
 */
// eslint-disable-next-line import/no-unresolved
import JoomlaDialog from 'joomla.dialog';

/**
 * Helper to create an element
 *
 * @param {String} nodeName
 * @param {String} text
 * @param {Array} classList
 * @returns {HTMLElement}
 */
const createEl = (nodeName, text = '', classList = []) => {
  const el = document.createElement(nodeName);
  el.textContent = text;
  if (classList && classList.length) {
    el.classList.add(...classList);
  }
  return el;
};

/**
 * Trigger the task through a GET request
 *
 * @param {String} url
 * @param {HTMLElement} resultContainer
 */
const runTheTask = (url, resultContainer) => {
  const statusHolder = resultContainer.querySelector('.scheduler-status');
  const progressBar = resultContainer.querySelector('.progress-bar');
  const complete = (success) => {
    progressBar.style.width = '100%';
    progressBar.classList.add(success ? 'bg-success' : 'bg-danger');
    setTimeout(() => progressBar.classList.remove('progress-bar-animated'), 500);
  };
  progressBar.style.width = '15%';

  fetch(url, { headers: { 'X-CSRF-Token': Joomla.getOptions('csrf.token', '') } })
    .then((response) => {
      if (!response.ok) {
        throw new Error(Joomla.Text._('JLIB_JS_AJAX_ERROR_OTHER').replace('%s', response.status).replace('%d', response.status));
      }
      return response.json();
    })
    .then((output) => {
      if (!output.data) {
        // The request was successful but the response is empty in some reason
        throw new Error(Joomla.Text._('JLIB_JS_AJAX_ERROR_NO_CONTENT'));
      }

      statusHolder.textContent = Joomla.Text._('COM_SCHEDULER_TEST_RUN_STATUS_COMPLETED');

      if (output.data.duration > 0) {
        resultContainer.appendChild(createEl('div', Joomla.Text._('COM_SCHEDULER_TEST_RUN_DURATION').replace('%s', output.data.duration.toFixed(2))));
      }

      if (output.data.output) {
        resultContainer.appendChild(createEl('div', Joomla.Text._('COM_SCHEDULER_TEST_RUN_OUTPUT').replace('%s', '').replace('<br>', '')));
        resultContainer.appendChild(createEl('pre', output.data.output, ['bg-body', 'p-2']));
      }

      complete(true);
    })
    .catch((error) => {
      complete(false);
      statusHolder.textContent = Joomla.Text._('COM_SCHEDULER_TEST_RUN_STATUS_TERMINATED');
      resultContainer.appendChild(createEl('div', error.message, ['text-danger']));
    });
};

// Listen on click over a task button to run a task
document.addEventListener('click', (event) => {
  const button = event.target.closest('button[data-scheduler-run]');
  if (!button) return;
  event.preventDefault();

  // Get the task info from the button
  const { id, title, url } = button.dataset;

  // Prepare the initial popup content, by following template:
  // <div class="p-3">
  // <h4>Task: Task title</h4>
  // <div class="mb-2 scheduler-status">Status: Task Status</div>
  // <div class="progress mb-2"><div class="progress-bar progress-bar-striped bg-success"></div></div>
  // </div>
  const content = (() => {
    const body = createEl('div', '', ['p-3']);
    const progress = createEl('div', '', ['progress', 'mb-2']);
    const progressBar = createEl('div', '', ['progress-bar', 'progress-bar-striped', 'progress-bar-animated']);
    body.appendChild(createEl('h4', Joomla.Text._('COM_SCHEDULER_TEST_RUN_TASK').replace('%s', title)));
    body.appendChild(createEl('div', Joomla.Text._('COM_SCHEDULER_TEST_RUN_STATUS_STARTED'), ['mb-2', 'scheduler-status']));
    progress.appendChild(progressBar);
    body.appendChild(progress);
    progressBar.style.width = '0%';
    return body;
  })();

  // Create dialog instance
  const dialog = new JoomlaDialog({
    popupType: 'inline',
    textHeader: Joomla.Text._('COM_SCHEDULER_TEST_RUN_TITLE').replace('%d', id),
    textClose: Joomla.Text._('JCLOSE'),
    popupContent: content,
    width: '800px',
    height: 'fit-content',
  });

  // Run the task when dialog is ready
  dialog.addEventListener('joomla-dialog:open', () => {
    runTheTask(url, content);
  });
  // Reload the page when dialog is closed
  dialog.addEventListener('joomla-dialog:close', () => {
    window.location.reload();
  });

  dialog.show();
});
