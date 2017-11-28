/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Field switcher
 */

!(function(document) {
	"use strict";

	function initSwitcher(event) {
		var target = event && event.target ? event.target : document;

		var switchers = target.querySelectorAll('.js-switcher'),
			switcher, nodes, parent;

		for (var i = 0; i < switchers.length; i++) {
			switcher = switchers[i];

			// Skip already initialized switcher
			if (switcher.getAttribute('data-switcher-set')) {
				continue;
			}
			switcher.setAttribute('data-switcher-set', '1');

			nodes    = switcher.querySelectorAll('input');
			parent   = nodes[1].parentNode;

			// Add the initial active class
			if (nodes[1].checked) {
				nodes[1].parentNode.classList.add('active');
				parent.nextElementSibling.querySelector('.switcher-label-' + nodes[1].value).classList.add('active');
			}
			else
			{
				parent.nextElementSibling.querySelector('.switcher-label-' + nodes[0].value).classList.add('active');
			}

			// Add the active class on click
			switcher.addEventListener('click', function(event) {
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
	}

	/**
	 * Initialize at an initial page load
	 */
	document.addEventListener("DOMContentLoaded", initSwitcher);

	/**
	 * Initialize when a part of the page was updated
	 */
	document.addEventListener("joomla:updated", initSwitcher);

})(document);
