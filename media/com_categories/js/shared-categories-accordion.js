/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (Joomla) {
  'use strict';

  if (!Joomla || !Joomla.Text) {
    throw new Error('core.js was not properly initialised');
  } // Selectors used by this script


  var buttonsSelector = '[id^=category-btn-]';
  /**
   * Handle the category toggle button click event
   * @param event
   */

  var handleCategoryToggleButtonClick = function handleCategoryToggleButtonClick(_ref) {
    var currentTarget = _ref.currentTarget;
    var button = currentTarget;
    var icon = button.querySelector('span'); // Toggle icon class

    icon.classList.toggle('fa-plus');
    icon.classList.toggle('fa-minus'); // Toggle aria label

    var ariaLabel = button.getAttribute('aria-label');
    button.setAttribute('aria-label', ariaLabel === Joomla.Text._('JGLOBAL_EXPAND_CATEGORIES') ? Joomla.Text._('JGLOBAL_COLLAPSE_CATEGORIES') : Joomla.Text._('JGLOBAL_EXPAND_CATEGORIES'));
  };
  /**
   * Script boot
   */


  var onBoot = function onBoot() {
    var buttons = [].slice.call(document.querySelectorAll(buttonsSelector));
    buttons.forEach(function (button) {
      button.addEventListener('click', handleCategoryToggleButtonClick);
    }); // Cleanup

    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(Joomla);