/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Update colour for selectbox
 */
Joomla = window.Joomla || {};

(function (Joomla) {
  'use strict';

  Joomla.updateSelectboxColour = function () {
    const colourSelects = document.querySelectorAll('.custom-select-color-state');
    for (let i = 0, l = colourSelects.length; i < l; i++) {
      // Add class on page load
      const selectBox = colourSelects[i];
      if (selectBox.value == 1) {
        selectBox.classList.add('custom-select-success');
      } else if (selectBox.value == 0) {
        selectBox.classList.add('custom-select-danger');
      }

      // Add class when value is changed
      selectBox.addEventListener('change', function () {
        const self = this;
        self.classList.remove('custom-select-success', 'custom-select-danger');
        if (self.value == 1) {
          self.classList.add('custom-select-success');
        } else if (self.value == 0 || self.value == parseInt(-2)) {
          self.classList.add('custom-select-danger');
        }
      });
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    Joomla.updateSelectboxColour();
  });
}(Joomla));
