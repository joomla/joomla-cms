/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (window, document, JText) {
  'use strict';

  // Selectors used by this script
  var formId = 'mailtoForm';
  var closeSelector = '.close-mailto';

  /**
   * Handle the form submit event
   * @param event
   */
  var handleFormSubmit = function (event) {
    event.preventDefault();
    var form = event.target;

    // Simple form validation
    if (form.mailto.value === '' || form.from.value === '') {
      alert(JText._('COM_MAILTO_EMAIL_ERR_NOINFO'));
      return;
    }

    form.submit();
  };

  /**
   * Register events
   */
  var registerEvents = function () {
    // Register the submit event listener
    document.getElementById(formId).addEventListener('submit', handleFormSubmit);

    // Register the close click listener
    var closeElements = [].slice.call(document.querySelectorAll(closeSelector));
    console.log(closeElements);
    closeElements.forEach(function (closeElement) {
      closeElement.addEventListener('click', function(event) {
        event.preventDefault();
        window.close();
      });
    });
  };

  document.addEventListener('DOMContentLoaded', function () {
    registerEvents();
  });

})(window, document, Joomla.JText);