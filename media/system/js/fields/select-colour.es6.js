/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  const onChange = ({
    target
  }) => {
    const self = target;
    const value = parseInt(self.value, 10);
    self.classList.remove('custom-select-success', 'custom-select-danger');

    if (value === 1) {
      self.classList.add('custom-select-success');
    } else if (value === 0 || value === -2) {
      self.classList.add('custom-select-danger');
    }
  };

  const updateSelectboxColour = () => {
    const colourSelects = [].slice.call(document.querySelectorAll('.custom-select-color-state'));
    colourSelects.forEach(colourSelect => {
      const value = parseInt(colourSelect.value, 10); // Add class on page load

      if (value === 1) {
        colourSelect.classList.add('custom-select-success');
      } else if (value === 0 || value === -2) {
        colourSelect.classList.add('custom-select-danger');
      } // Add class when value is changed


      colourSelect.addEventListener('change', onChange);
    }); // Cleanup

    document.removeEventListener('DOMContentLoaded', updateSelectboxColour, true);
  }; // On docunment loaded


  document.addEventListener('DOMContentLoaded', updateSelectboxColour, true); // On Joomla updated

  document.addEventListener('joomla:updated', updateSelectboxColour, true);
})();