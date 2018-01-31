/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

Joomla = window.Joomla || {};

(function(Joomla, document) {
	'use strict';

	function closest(element, selector) {
		var matchesFn;

		// find vendor prefix
		['matches', 'msMatchesSelector'].some(function(fn) {
			if (typeof document.body[fn] == 'function') {
				matchesFn = fn;
				return true;
			}
			return false;
		})

		var parent;

		// Traverse parents
		while (element) {
			parent = element.parentElement;
			if (parent && parent[matchesFn](selector)) {
				return parent;
			}
			element = parent;
		}

		return null;
	}

	function initPageContentStuff(event) {
		var target = event && event.target ? event.target : document;

		/**
		 * Turn radios into btn-group
		 */
		var container = target.querySelectorAll('.btn-group');
		for (var i = 0; i < container.length; i++) {
			var labels = container[i].querySelectorAll('label');
			for (var j = 0; j < labels.length; j++) {
				labels[j].classList.add('btn');
				if ((j % 2) == 1) {
					labels[j].classList.add('btn-outline-danger');
				} else {
					labels[j].classList.add('btn-outline-success');

				}
			}
		}

		var btnNotActive = target.querySelector('.btn-group label:not(.active)');
		if (btnNotActive) {
			btnNotActive.addEventListener('click', function(event) {
				var input = document.getElementById(event.target.getAttribute('for'));

				if (input.getAttribute('checked') !== 'checked') {
					var label = closest(event.target, '.btn-group').querySelector('label');
					label.classList.remove('active');
					label.classList.remove('btn-success');
					label.classList.remove('btn-danger');
					label.classList.remove('btn-primary');

					if (closest(label, '.btn-group').classList.contains('btn-group-reversed')) {
						if (!label.classList.contains('btn')) label.classList.add('btn');
						if (input.value === '') {
							label.classList.add('active');
							label.classList.add('btn');
							label.classList.add('btn-outline-primary');
						} else if (input.value === 0) {
							label.classList.add('active');
							label.classList.add('btn');
							label.classList.add('btn-outline-success');
						} else {
							label.classList.add('active');
							label.classList.add('btn');
							label.classList.add('btn-outline-danger');
						}
					} else {
						if (input.value === '') {
							label.classList.add('active');
							label.classList.add('btn');
							label.classList.add('btn-outline-primary');
						} else if (input.value === 0) {
							label.classList.add('active');
							label.classList.add('btn');
							label.classList.add('btn-outline-danger');
						} else {
							label.classList.add('active');
							label.classList.add('btn');
							label.classList.add('btn-outline-success');
						}
					}
					input.setAttribute('checked', true);
					//input.dispatchEvent('change');
				}
			});
		}

		var btsGrouped = target.querySelectorAll('.btn-group input[checked=checked]');
		for (var i = 0, l = btsGrouped.length; l>i; i++) {
			var self   = btsGrouped[i],
				attrId = self.id,
				label = target.querySelector('label[for=' + attrId + ']');
			if (self.parentNode.parentNode.classList.contains('btn-group-reversed')) {
				if (self.value === '') {
					label.classList.add('active');
					label.classList.add('btn');
					label.classList.add('btn-outline-primary');
				} else if (self.value === 0) {
					label.classList.add('active');
					label.classList.add('btn');
					label.classList.add('btn-outline-success');
				} else {
					label.classList.add('active');
					label.classList.add('btn');
					label.classList.add('btn-outline-danger');
				}
			} else {
				if (self.value === '') {
					label.classList.add('active');
					label.classList.add('btn-outline-primary');
				} else if (self.value === 0) {
					label.classList.add('active');
					label.classList.add('btn');
					label.classList.add('btn-outline-danger');
				} else {
					label.classList.add('active');
					label.classList.add('btn');
					label.classList.add('btn-outline-success');
				}
			}
		}
	}

	/**
	 * Initialize when a part of the page was updated
	 */
	document.addEventListener('joomla:updated', initPageContentStuff);

})(Joomla, document);
