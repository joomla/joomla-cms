/**
  * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
  * @license    GNU General Public License version 2 or later; see LICENSE.txt
  */

(() => {
  'use strict';

  /**
    * Javascript to insert the onclick event
    * We get the position associated to the button that has been clicked
    * and we use it to set the position of joomla field fancy select element
    * of the parent window.
    */

  document.addEventListener('DOMContentLoaded', () => {
    // Get the elements
    const elements = document.querySelectorAll('.jmod-position-select');

    for (let i = 0, l = elements.length; l > i; i += 1) {
      // Listen for click event
      elements[i].addEventListener('click', (event) => {
        const position = event.target.getAttribute('data-position');

        // Select value of the choices field by triggering mousedown event on the position option
        const elPositionSelect = window.parent.parent.document.querySelector('#jform_position');
        const elPositionOptions = elPositionSelect.closest('.choices').querySelectorAll('.choices__item--selectable');
        for (let j = 0; j < elPositionOptions.length; j += 1) {
          if (elPositionOptions[j].dataset.value === position) {
            elPositionOptions[j].dispatchEvent(new Event('mousedown'));
          }
        }

        if (window.parent.parent.Joomla.Modal) {
          window.parent.parent.Joomla.Modal.getCurrent().close();
        }
      });
    }
  });
})();
