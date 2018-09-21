/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((window, document, Joomla) => {
  'use strict';

  if (!Joomla || typeof Joomla.JText._ !== 'function') {
    throw new Error('core.js was not properly initialised');
  }

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
      // @todo use the Joomla alerts here
      alert(Joomla.JText._('COM_MAILTO_EMAIL_ERR_NOINFO'));
      return;
    }

    form.submit();
  };

  /**
   * Register events
   */
  const onClick = (event) => {
    event.preventDefault();
    window.close();
  };

  /**
   * Register events
   */
  const registerEvents = () => {
    // Register the submit event listener
    document.getElementById(formId).addEventListener('submit', handleFormSubmit);

    // Register the close click listener
    const closeElements = [].slice.call(document.querySelectorAll(closeSelector));

    if (closeElements.length) {
      closeElements.forEach((closeElement) => {
        closeElement.addEventListener('click', onClick);
      });
    }

    // Cleanup
    document.removeEventListener('DOMContentLoaded', registerEvents);
  };

  document.addEventListener('DOMContentLoaded', registerEvents);
})(window, document, Joomla);
