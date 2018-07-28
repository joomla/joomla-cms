/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(function() {
	document.addEventListener('DOMContentLoaded', function() {

		var batchSelector;

		var batchCategory = document.getElementById('batch-category-id');
		if (batchCategory) {
			batchSelector = batchCategory;
		}

		var batchMenu = document.getElementById('batch-menu-id');
		if (batchMenu) {
			batchSelector = batchMenu;
		}

		var batchPosition = document.getElementById('batch-position-id');
		if (batchPosition) {
			batchSelector = batchPosition;
		}

		var batchCopyMove = document.getElementById('batch-copy-move');
		if (batchCopyMove) {
			batchSelector.addEventListener('change', function(){
				if (batchSelector.value != 0 || batchSelector.value !== '') {
					batchCopyMove.style.display = 'block';
				} else {
					batchCopyMove.style.display = 'none';
				}
			});
		}

	});
})();
