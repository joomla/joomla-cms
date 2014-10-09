/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

jQuery(function ($) {
	$('.popup').click(function (event) {
		event.preventDefault();
		var width = 700;
		var height = 500;
		var toppx = ($(window).height() / 2) - (height / 2);
		var leftpx = ($(window).width() / 2) - (width / 2);
		window.open($(this).attr("href"), "popupWindow", "width=" + width + ",height=" + height + ",scrollbars=yes,left=" + leftpx + "top=" + toppx);
	});
});
