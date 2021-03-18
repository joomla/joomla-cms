/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(Joomla => {
  'use strict';

  if (!Joomla) {
    window.Joomla = {};
  }

  const onBoot = () => {
    Joomla.submitbutton = pressbutton => {
      if (pressbutton === 'filters.delete' && !window.confirm(Joomla.JText._('COM_FINDER_INDEX_CONFIRM_DELETE_PROMPT'))) {
        return false;
      }

      Joomla.submitform(pressbutton);
      return true;
    }; // Cleanup


    document.addEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(Joomla);