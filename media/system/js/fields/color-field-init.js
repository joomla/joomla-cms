/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

!(function(document, $) {
	"use strict";

	function initSimpleColorField (event) {
		$(event.target).find('select.simplecolors').simplecolors();
	}

	/**
	 * Initialize at an initial page load
	 */
	document.addEventListener("DOMContentLoaded", initSimpleColorField);

	/**
	 * Initialize when a part of the page was updated
	 */
	document.addEventListener("joomla:updated", initSimpleColorField);

})(document, jQuery);
