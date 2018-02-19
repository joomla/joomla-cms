/**
 * @package         Joomla.JavaScript
 * @copyright       Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Copy title to the menu tile and alias to menu alias respectively
 */
(function ($) {
	$(document).ready(function() {
		$("#jform_title").on("keyup", function () {
			$("#jform_menutitle").val($(this).val());
		});

		$("#jform_name").on("keyup", function () {
			$("#jform_menutitle").val($(this).val());
		});

		$("#jform_alias").on("keyup", function () {
			$("#jform_menualias").val($(this).val());
		});
	});
}(jQuery));
