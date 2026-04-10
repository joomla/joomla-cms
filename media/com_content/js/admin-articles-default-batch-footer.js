/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((document, submitForm) => {

  // Selectors used by this script
  const buttonDataSelector = 'data-submit-task';
  const formId = 'adminForm';

  /**
   * Submit the task
   * @param task
   */
  const submitTask = task => {
    const form = document.getElementById(formId);
    if (form && task === 'article.batch') {
      submitForm(task, form);
    }
  };

  // Register events
  document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('batch-submit-button-id');
    if (button) {
      button.addEventListener('click', e => {
        const task = e.target.getAttribute(buttonDataSelector);
        submitTask(task);
        return false;
      });
    }
  });
})(document, Joomla.submitform);
