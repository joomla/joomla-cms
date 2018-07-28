/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(function () {
  document.addEventListener('DOMContentLoaded', () => {
    let batchSelector;

    const batchCategory = document.getElementById('batch-category-id');
    if (batchCategory) {
      batchSelector = batchCategory;
    }

    const batchMenu = document.getElementById('batch-menu-id');
    if (batchMenu) {
      batchSelector = batchMenu;
    }

    const batchPosition = document.getElementById('batch-position-id');
    if (batchPosition) {
      batchSelector = batchPosition;
    }

    const batchCopyMove = document.getElementById('batch-copy-move');
    if (batchCopyMove) {
      batchSelector.addEventListener('change', () => {
        if (batchSelector.value != 0 || batchSelector.value !== '') {
          batchCopyMove.style.display = 'block';
        } else {
          batchCopyMove.style.display = 'none';
        }
      });
    }
  });
}());
