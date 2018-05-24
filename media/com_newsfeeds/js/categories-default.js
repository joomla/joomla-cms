/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (JText) {
  'use strict';

  // Selectors used by this script
  var buttonsSelector = '[id^=category-btn-]';

  /**
   * Handle the category toggle button click event
   * @param event
   */
  var handleCategoryToggleButtonClick = function (event) {
    var button = event.currentTarget;
    var icon = button.querySelector('span');

    // Toggle icon class
    icon.classList.toggle('icon-plus');
    icon.classList.toggle('icon-minus');

    // Toggle aria label
    var ariaLabel = button.getAttribute('aria-label');
    button.setAttribute('aria-label', (
      ariaLabel === JText._('JGLOBAL_EXPAND_CATEGORIES') ? JText._('JGLOBAL_COLLAPSE_CATEGORIES') : JText._('JGLOBAL_EXPAND_CATEGORIES')
    ));
  };

  /**
   * Register the events
   */
  var registerEvents = function () {
    var buttons = [].slice.call(document.querySelectorAll(buttonsSelector));
    buttons.forEach(function (button) {
      button.addEventListener('click', handleCategoryToggleButtonClick);
    });
  };

  document.addEventListener('DOMContentLoaded', function () {
    registerEvents();
  });
})(Joomla.JText);
