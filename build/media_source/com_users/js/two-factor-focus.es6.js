/**
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const elCodeField = document.getElementById('users-tfa-code');
    const elValidateButton = document.getElementById('users-tfa-captive-button-submit');

    if (elCodeField && elCodeField.style.display !== 'none' && !elCodeField.classList.contains('visually-hidden')) {
      elCodeField.focus();
    } else if (elValidateButton) {
      elValidateButton.focus();
    }
  });
})();
