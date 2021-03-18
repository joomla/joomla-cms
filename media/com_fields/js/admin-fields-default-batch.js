/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  var batchSelector = document.getElementById('batch-group-id');
  var batchCopyMove = document.getElementById('batch-copy-move');
  batchCopyMove.classList.add('hidden');
  batchSelector.addEventListener('change', function () {
    if (batchSelector.value === 'nogroup' || batchSelector.value !== '') {
      batchCopyMove.classList.remove('hidden');
    } else {
      batchCopyMove.classList.add('hidden');
    }
  }, false);
});