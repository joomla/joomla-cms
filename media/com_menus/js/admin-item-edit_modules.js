/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function() {
	"use strict";

	var options = Joomla.getOptions('menus-edit-modules');

	if (options) {
		window.viewLevels = options.viewLevels;
		window.menuId = parseInt(options.itemId);
	}

	document.addEventListener('DOMContentLoaded', function() {
		document.getElementById("jform_toggle_modules_assigned1").addEventListener("click", function (event) {
			var list = document.querySelectorAll("tr.no");
			list.forEach(function(item) {
				item.style.display = 'table-row';
			});
		});

		document.getElementById("jform_toggle_modules_assigned0").addEventListener("click", function (event) {
			var list = document.querySelectorAll("tr.no");
			list.forEach(function (item) {
				item.style.display = 'none';
			});
		});

		document.getElementById("jform_toggle_modules_published1").addEventListener("click", function (event) {
			var list = document.querySelectorAll(".table tr.unpublished");
			list.forEach(function (item) {
				item.style.display = 'table-row';
			});
		});

		document.getElementById("jform_toggle_modules_published0").addEventListener("click", function (event) {
			var list = document.querySelectorAll(".table tr.unpublished");
			list.forEach(function (item) {
				item.style.display = 'none';
			});
		});
	});
})();
