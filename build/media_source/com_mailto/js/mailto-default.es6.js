/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((window, document, Joomla) => {
  'use strict';

  if (!Joomla || typeof Joomla.JText._ !== 'function') {
    throw new Error('core.js was not properly initialised');
  }

  // Selectors used by this script
  const closeSelector = '.close-mailto';

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
