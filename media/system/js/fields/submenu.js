/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

jQuery(document).ready(function($) {
	if (window.toggleSidebar) {
		toggleSidebar(true);
	} else {
		$("#j-toggle-sidebar-header").css("display", "none");
		$("#j-toggle-button-wrapper").css("display", "none");
	}
});
