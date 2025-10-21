/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  const onChange = ({ target }) => {
    const self = target;
    const value = parseInt(self.value, 10);

    self.classList.remove('form-select-success', 'form-select-danger');

    if (value === 1) {
      self.classList.add('form-select-success');
    } else if (value === 0 || value === -2) {
      self.classList.add('form-select-danger');
    }
  };

  const updateSelectboxColour = () => {
    document.querySelectorAll('.form-select-color-state').forEach((colourSelect) => {
      const value = parseInt(colourSelect.value, 10);

      // Add class on page load
      if (value === 1) {
        colourSelect.classList.add('form-select-success');
      } else if (value === 0 || value === -2) {
        colourSelect.classList.add('form-select-danger');
      }

      // Add class when value is changed
      colourSelect.addEventListener('change', onChange);
    });

    // Cleanup
    document.removeEventListener('DOMContentLoaded', updateSelectboxColour, true);
  };

  // On document loaded
  document.addEventListener('DOMContentLoaded', updateSelectboxColour, true);

  // On Joomla updated
  document.addEventListener('joomla:updated', updateSelectboxColour, true);
})();
