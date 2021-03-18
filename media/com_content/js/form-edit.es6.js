/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((document, submitForm) => {
  'use strict'; // Selectors used by this script

  const buttonDataSelector = 'data-submit-task';
  const formId = 'adminForm';
  /**
   * Submit the task
   * @param task
   */

  const submitTask = task => {
    const form = document.getElementById(formId);

    if (task === 'article.cancel' || document.formvalidator.isValid(form)) {
      submitForm(task, form);
    }
  }; // Register events


  document.addEventListener('DOMContentLoaded', () => {
    const buttons = [].slice.call(document.querySelectorAll(`[${buttonDataSelector}]`));
    buttons.forEach(button => {
      button.addEventListener('click', e => {
        e.preventDefault();
        const task = e.target.getAttribute(buttonDataSelector);
        submitTask(task);
      });
    });
  });
})(document, Joomla.submitform);