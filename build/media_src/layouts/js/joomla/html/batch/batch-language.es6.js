/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  const onSelect = () => {
    const batchCategory = document.getElementById('batch-category-id');
    const batchMenu = document.getElementById('batch-menu-id');
    const batchPosition = document.getElementById('batch-position-id');
    const batchCopyMove = document.getElementById('batch-copy-move');
    let batchSelector;

    const onChange = () => {
      if (!batchSelector.value ||
          (batchSelector.value && parseInt(batchSelector.value, 10) === 0)) {
        batchCopyMove.style.display = 'none';
      } else {
        batchCopyMove.style.display = 'block';
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
      batchCopyMove.style.display = 'none';
    }

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
