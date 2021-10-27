/**
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Run the test task in the scheduler backend list
 *
 * Used for the play button in the list view
 *
 * @package  Joomla.Components
 * @subpackage Scheduler.Tasks
 *
 * @since    __DEPLOY_VERSION__
 */
if (!window.Joomla) {
  throw new Error('Joomla API was not properly initialised');
}

const initRunner = () => {
  const modal = document.getElementById('scheduler-test-modal');

  const template = `
    <h4 class="scheduler-headline">${Joomla.Text._('COM_SCHEDULER_TEST_RUN_TASK')}</h4>
    <div>${Joomla.Text._('COM_SCHEDULER_TEST_RUN_STATUS_STARTED')}</div>
    <div class="mt-3 text-center"><span class="fa fa-spinner fa-spin fa-lg"></span></div>
  `;

  const paths = Joomla.getOptions('system.paths');
  const uri = `${paths ? `${paths.base}/index.php` : window.location.pathname}?option=com_ajax&format=json&plugin=RunSchedulerTest&group=system&id=%d`;

  modal.addEventListener('show.bs.modal', (e) => {
    const button = e.relatedTarget;
    const id = parseInt(button.dataset.id, 10);
    const { title } = button.dataset;

    modal.querySelector('.modal-title').innerHTML = Joomla.Text._('COM_SCHEDULER_TEST_RUN_TITLE').replace('%d', id);
    modal.querySelector('.modal-body > div').innerHTML = template.replace('%s', title);

    Joomla.request({
      url: uri.replace('%d', id),
      onSuccess: (data, xhr) => {
        [].slice.call(modal.querySelectorAll('.modal-body > div > div')).forEach((el) => {
          el.parentNode.removeChild(el);
        });

        const output = JSON.parse(data);

        if (output && output.success && output.data && output.data.status === 0) {
          modal.querySelector('.modal-body > div').innerHTML += `<div>${Joomla.Text._('COM_SCHEDULER_TEST_RUN_STATUS_COMPLETED')}</div>`;

          if (output.data.duration > 0) {
            modal.querySelector('.modal-body > div').innerHTML += `<div>${Joomla.Text._('COM_SCHEDULER_TEST_RUN_DURATION').replace('%s', output.data.duration.toFixed(2))}</div>`;
          }

          if (output.data.output) {
            const result = output.data.output
              .replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;')
              .replace(/'/g, '&#039;')
              .replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2');

            modal.querySelector('.modal-body > div').innerHTML += `<div>${Joomla.Text._('COM_SCHEDULER_TEST_RUN_OUTPUT').replace('%s', result)}</div>`;
          }
        } else {
          modal.querySelector('.modal-body > div').innerHTML += `<div>${Joomla.Text._('COM_SCHEDULER_TEST_RUN_STATUS_TERMINATED')}</div>`;
          modal.querySelector('.modal-body > div').innerHTML += `<div>${Joomla.Text._('COM_SCHEDULER_TEST_RUN_OUTPUT').replace('%s', Joomla.Text._('JLIB_JS_AJAX_ERROR_OTHER').replace('%s', xhr.status))}</div>`;
        }
      },
      onError: (xhr) => {
        modal.querySelector('.modal-body > div').innerHTML += `<div>${Joomla.Text._('COM_SCHEDULER_TEST_RUN_STATUS_TERMINATED')}</div>`;

        const msg = Joomla.ajaxErrorsMessages(xhr);
        modal.querySelector('.modal-body > div').innerHTML += `<div>${Joomla.Text._('COM_SCHEDULER_TEST_RUN_OUTPUT').replace('%s', msg.error)}</div>`;
      },
    });
  });
};

((document) => {
  document.addEventListener('DOMContentLoaded', () => {
    initRunner();
  });
})(document);
