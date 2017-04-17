/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(function(Joomla) {
	"use strict";

	Joomla.menuHide = function(value) {
		if (value == 0 || value == '-') {
			document.getElementById('menuselect-group').style.display = 'none';
		} else {
			document.getElementById('menuselect-group').style.display = 'block';
		}
	};

	document.addEventListener('DOMContentLoaded', function() {
		Joomla.menuHide(document.getElementById('jform_assignment').value);

		document.getElementById('jform_assignment').addEventListener('change', function(event) {
			Joomla.menuHide(event.target.value);
		});
	});
})(Joomla);

