/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((document, submitForm) => {
  'use strict';

  // Selectors used by this script
  const buttonDataSelector = 'data-submit-task';

  /**
   * Submit the task
   * @param task
   * @param form
   */
  const submitTask = (task, form) => {
    if (task === 'config.cancel' || document.formvalidator.isValid(form)) {
      submitForm(task, form);
    }
  };

  /**
   * Register events
   */
  const registerEvents = () => {
    document.querySelectorAll(`[${buttonDataSelector}]`).forEach((button) => {
      button.addEventListener('click', (e) => {
        e.preventDefault();
        const task = e.currentTarget.getAttribute(buttonDataSelector);
        submitTask(task, e.currentTarget.form);
      });
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    registerEvents();
  });
})(document, Joomla.submitform);
