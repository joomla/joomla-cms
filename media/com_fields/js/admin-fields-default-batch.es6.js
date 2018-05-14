/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

document.addEventListener('DOMContentLoaded', () => {
  const batchSelector = document.getElementById('batch-group-id');
  const batchCopyMove = document.getElementById('batch-copy-move');
  batchCopyMove.style.display = 'none';

  batchSelector.addEventListener('change', () => {
    if (batchSelector.value === 'nogroup') {
      batchCopyMove.style.display = 'block';
    } else {
      batchCopyMove.style.display = 'none';
    }
  }, false);
});
