/**
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const elSaveButton = document.getElementById('user-mfa-edit-save');

    // Capture the admin toolbar save button, make it click the inline save button
    document.querySelectorAll('.button-user-mfa-edit-save').forEach((elButton) => {
      elButton.addEventListener('click', (e) => {
        e.preventDefault();

        if (elSaveButton) {
          elSaveButton.click();
        }
      });
    });
  });
})();
