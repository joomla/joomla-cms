/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (document, submitForm) {
  'use strict'; // Selectors used by this script

  var buttonDataSelector = 'data-submit-task';
  var formId = 'adminForm';
  /**
   * Submit the task
   * @param task
   */

  var submitTask = function submitTask(task) {
    var form = document.getElementById(formId);

    if (task === 'article.cancel' || document.formvalidator.isValid(form)) {
      submitForm(task, form);
    }
  }; // Register events


  document.addEventListener('DOMContentLoaded', function () {
    var buttons = [].slice.call(document.querySelectorAll("[".concat(buttonDataSelector, "]")));
    buttons.forEach(function (button) {
      button.addEventListener('click', function (e) {
        e.preventDefault();
        var task = e.target.getAttribute(buttonDataSelector);
        submitTask(task);
      });
    });
  });
})(document, Joomla.submitform);