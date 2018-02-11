/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

jQuery(document).ready(function($) {
	if ($("#batch-category-id").length){
		var batchSelector = $("#batch-category-id");
	}
	if ($("#batch-menu-id").length){
		var batchSelector = $("#batch-menu-id");
	}
	if ($("#batch-position-id").length){
		var batchSelector = $("#batch-position-id");
	}
	if ($("#batch-copy-move").length && batchSelector) {
		$("#batch-copy-move").hide();
		batchSelector.on("change", function(){
			if (batchSelector.val() != 0 || batchSelector.val() != "") {
				$("#batch-copy-move").show();
			} else {
				$("#batch-copy-move").hide();
			}
		});
	}
});
