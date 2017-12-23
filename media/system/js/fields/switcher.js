/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
			var nodes  = switcher[i].querySelectorAll('input'),
				parent = nodes[1].parentNode;

			if (nodes[1].checked) {
				nodes[1].parentNode.classList.add('active');
				parent.nextElementSibling.querySelector('.switcher-label-' + nodes[1].value).classList.add('active');
			}
			else
			{
				parent.nextElementSibling.querySelector('.switcher-label-' + nodes[0].value).classList.add('active');
			}

			// Add the active class on click
			switcher[i].addEventListener('click', function(event) {
				var el     = event.target,
					parent = el.parentNode,
					spans  = parent.nextElementSibling.querySelectorAll('span');

				for (var i = 0; i < spans.length; i++) {
					spans[i].classList.remove('active');
				}

				if (!el.classList.contains('active')) {
					parent.classList.add('active');
				}
				else {
					parent.classList.remove('active');
				}

				parent.nextElementSibling.querySelector('.switcher-label-' + el.value).classList.add('active');

			});

		}
	});

})();
