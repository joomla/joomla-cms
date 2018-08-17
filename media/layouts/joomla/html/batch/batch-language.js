/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(function () {
  var batchCategory = document.getElementById('batch-category-id');
  var batchMenu = document.getElementById('batch-menu-id');
  var batchPosition = document.getElementById('batch-position-id');
  var batchCopyMove = document.getElementById('batch-copy-move');
  var batchSelector = void 0;

  var onChange = function onChange() {
    if (batchSelector.value !== 0 || batchSelector.value !== '') {
      batchCopyMove.style.display = 'block';
    } else {
      batchCopyMove.style.display = 'none';
    }
  };

  var onSelect = function onSelect() {
    if (batchCategory) {
      batchSelector = batchCategory;
    }

    if (batchMenu) {
      batchSelector = batchMenu;
    }

    if (batchPosition) {
      batchSelector = batchPosition;
    }

    batchCopyMove.style.display = 'none';

    if (batchCopyMove) {
      batchSelector.addEventListener('change', onChange);
    }

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onSelect, true);
  };

  // Document loaded
  document.addEventListener('DOMContentLoaded', onSelect, true);

  // Joomla updated
  document.addEventListener('joomla:updated', onSelect, true);
})();
