/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
  var onSelect = function onSelect() {
    var batchCategory = document.getElementById('batch-category-id');
    var batchMenu = document.getElementById('batch-menu-id');
    var batchPosition = document.getElementById('batch-position-id');
    var batchCopyMove = document.getElementById('batch-copy-move');
    var batchSelector;

    var onChange = function onChange() {
      if (!batchSelector.value || batchSelector.value && parseInt(batchSelector.value, 10) === 0) {
        batchCopyMove.classList.add('hidden');
      } else {
        batchCopyMove.classList.remove('hidden');
      }
    };

    if (batchCategory) {
      batchSelector = batchCategory;
    }

    if (batchMenu) {
      batchSelector = batchMenu;
    }

    if (batchPosition) {
      batchSelector = batchPosition;
    }

    if (batchCopyMove) {
      batchCopyMove.classList.add('hidden');
    }

    if (batchCopyMove) {
      batchSelector.addEventListener('change', onChange);
    } // Cleanup


    document.removeEventListener('DOMContentLoaded', onSelect, true);
  }; // Document loaded


  document.addEventListener('DOMContentLoaded', onSelect, true); // Joomla updated

  document.addEventListener('joomla:updated', onSelect, true);
})();