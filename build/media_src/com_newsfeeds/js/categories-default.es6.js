/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((JText) => {
  'use strict';

  // Selectors used by this script
  const buttonsSelector = '[id^=category-btn-]';

  /**
   * Handle the category toggle button click event
   * @param event
   */
  const handleCategoryToggleButtonClick = (event) => {
    const button = event.currentTarget;
    const icon = button.querySelector('span');

    // Toggle icon class
    icon.classList.toggle('icon-plus');
    icon.classList.toggle('icon-minus');

    // Toggle aria label
    const ariaLabel = button.getAttribute('aria-label');
    button.setAttribute(
      'aria-label',
      (ariaLabel === JText._('JGLOBAL_COLLAPSE_CATEGORIES')
        || JText._('JGLOBAL_EXPAND_CATEGORIES')),
    );
  };

  /**
   * Register the events
   */
  const registerEvents = () => {
    const buttons = [].slice.call(document.querySelectorAll(buttonsSelector));
    buttons.forEach((button) => {
      button.addEventListener('click', handleCategoryToggleButtonClick);
    });

    // Cleanup
    document.removeEventListener('DOMContentLoaded', registerEvents);
  };

  document.addEventListener('DOMContentLoaded', registerEvents);
})(Joomla.JText);
