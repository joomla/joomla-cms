/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    // We need to use JS to move the modal before the closing body tag to avoid stacking issues
    const multilangueModal = document.getElementById('multiLangModal');

    if (multilangueModal) {
      // Clone the modal element
      const clone = multilangueModal.cloneNode(true); // Remove the original modal element

      multilangueModal.parentNode.removeChild(multilangueModal); // Append clone before closing body tag

      document.body.appendChild(clone);
    }
  });
})();