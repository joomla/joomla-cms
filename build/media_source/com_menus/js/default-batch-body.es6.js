/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  const batchMenu = document.getElementById('batch-menu-id');
  const batchCopyMove = document.getElementById('batch-copy-move');
  let batchSelector;

  const onChange = () => {
    if (batchSelector.value !== 0 || batchSelector.value !== '') {
      batchCopyMove.style.display = 'block';
    } else {
      batchCopyMove.style.display = 'none';
    }
  };

  const onSelect = () => {
    if (batchMenu) {
      batchSelector = batchMenu;
    }

    if (batchCopyMove) {
      batchSelector.addEventListener('change', onChange);
    }

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onSelect, true);
  };

  // Document loaded
  document.addEventListener('DOMContentLoaded', onSelect, true);
})();
