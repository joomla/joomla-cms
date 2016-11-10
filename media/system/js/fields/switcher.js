/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Field switcher
 */

(function() {
	"use strict";

	document.addEventListener('DOMContentLoaded', function() {

		var switcher = document.querySelectorAll('.js-switcher');

		for (var i = 0; i < switcher.length; i++) {

			// Add the initial active class
			var nodes = switcher[i].querySelectorAll('input');
			if (nodes[1].checked) {
				nodes[1].parentNode.classList.add('active');
			}

			// Add the active class on click
			switcher[i].addEventListener('click', function(event) {
				var el = event.target;

				if (!el.classList.contains('active')) {
					el.parentNode.classList.add('active');
				}
				else {
					el.parentNode.classList.remove('active');
				}

			});

		}
	});
})();
