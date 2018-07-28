/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((window, document, JText) => {
  'use strict';

  // Selectors used by this script
  const formId = 'mailtoForm';
  const closeSelector = '.close-mailto';

  /**
   * Handle the form submit event
   * @param event
   */
  const handleFormSubmit = (event) => {
    event.preventDefault();
    const form = event.target;

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
  const registerEvents = () => {
    // Register the submit event listener
    document.getElementById(formId).addEventListener('submit', handleFormSubmit);

    // Register the close click listener
    const closeElements = [].slice.call(document.querySelectorAll(closeSelector));

    closeElements.forEach((closeElement) => {
      closeElement.addEventListener('click', (event) => {
        event.preventDefault();
        window.close();
      });
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    registerEvents();
  });
})(window, document, Joomla.JText);
