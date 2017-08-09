/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Fix the alignment of the Options and Help toolbar buttons
 */

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		var toolbarOptions = document.getElementById('toolbar-options'),
		    toolbarHelp    = document.getElementById('toolbar-help');

		if (toolbarHelp && !toolbarOptions) {
			toolbarHelp.classList.add('ml-auto');
		}
		if (toolbarOptions && !toolbarHelp) {
			toolbarOptions.classList.add('ml-auto');
		}
	});

})();
