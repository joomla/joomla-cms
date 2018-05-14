/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (document, submitForm) {
  'use strict';

  // Selectors used by this script
  var buttonDataSelector = 'data-submit-task';
  var formId = 'application-form';

  /**
   * Submit the task
   * @param task
   */
  var submitTask = function (task) {
    var form = document.getElementById(formId);
    if (task == 'config.cancel' || document.formvalidator.isValid(form)) {
      submitForm(task, form);
    }
  };

  /**
   * Register events
   */
  var registerEvents = function () {
    var buttons = [].slice.call(document.querySelectorAll('[' + buttonDataSelector + ']'));
    buttons.forEach(function (button) {
      button.addEventListener('click', function (e) {
        e.preventDefault();
        var task = e.target.getAttribute(buttonDataSelector);
        submitTask(task);
      });
    });
  };

  document.addEventListener('DOMContentLoaded', function () {
    registerEvents();
  });

})(document, Joomla.submitform);
