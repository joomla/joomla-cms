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
  /**
   * Submit the task
   * @param task
   * @param form
   */

  var submitTask = function submitTask(task, form) {
    if (task === 'modules.cancel' || document.formvalidator.isValid(form)) {
      submitForm(task, form);
    }
  };
  /**
   * Register events
   */


  var registerEvents = function registerEvents() {
    var buttons = [].slice.call(document.querySelectorAll("[".concat(buttonDataSelector, "]")));
    buttons.forEach(function (button) {
      button.addEventListener('click', function (e) {
        e.preventDefault();
        var task = e.currentTarget.getAttribute(buttonDataSelector);
        submitTask(task, e.currentTarget.form);
      });
    });
  };

  document.addEventListener('DOMContentLoaded', function () {
    registerEvents();
  });
})(document, Joomla.submitform);