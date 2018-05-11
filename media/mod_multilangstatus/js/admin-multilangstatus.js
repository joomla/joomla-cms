/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {

		// We need to use JS to move the modal before the closing body tag to avoid stacking issues
		var multilangueModal = document.getElementById('multiLangModal');

		if (multilangueModal) {
			// Clone the modal element
			var clone = multilangueModal.cloneNode(true);

			// Remove the original modal element
			multilangueModal.parentNode.removeChild(multilangueModal);

			// Append clone before closing body tag
			document.body.appendChild(clone);
		}

	});

})();
