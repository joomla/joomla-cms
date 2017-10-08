/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

Joomla = window.Joomla || {};

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {

		/**
		 * Turn radios into btn-group
		 */
		var container = document.querySelectorAll('.btn-group');
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

		var btnNotActive = document.querySelector('.btn-group label:not(.active)');
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

		var btsGrouped = document.querySelectorAll('.btn-group input[checked=checked]');
		for (var i = 0, l = btsGrouped.length; l>i; i++) {
			var self   = btsGrouped[i],
			    attrId = self.id,
			    label = document.querySelector('label[for=' + attrId + ']');
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

		/**
		 * Sticky Toolbar
		 */
		var navTop;
		var isFixed = false;

		processScrollInit();
		processScroll();

		document.addEventListener('resize', processScrollInit, false);
		document.addEventListener('scroll', processScroll);

		function processScrollInit() {
			var subhead = document.getElementById('subhead');

			if (subhead) {
				navTop = document.querySelector('.subhead').offsetHeight;

				if (document.getElementById('sidebar-wrapper') && document.getElementById('sidebar-wrapper').style.display === 'none') {
					subhead.style.left = 0;
				}

				// Only apply the scrollspy when the toolbar is not collapsed
				if (document.body.clientWidth > 480) {
					document.querySelector('.subhead-collapse').style.height = document.querySelector('.subhead').style.height;
					subhead.style.width = 'auto';
				}
			}
		}

		function processScroll() {
			var subhead = document.getElementById('subhead');

			if (subhead) {
				var scrollTop = (window.pageYOffset || subhead.scrollTop)  - (subhead.clientTop || 0);

				if (scrollTop >= navTop && !isFixed) {
					isFixed = true;
					subhead.classList.add('subhead-fixed');

					if (document.getElementById('sidebar-wrapper') && document.getElementById('sidebar-wrapper').style.display === 'none') {
						subhead.style.left = 0;
					}
				} else if (scrollTop <= navTop && isFixed) {
					isFixed = false;
					subhead.classList.remove('subhead-fixed');
				}
			}
		}
	});
})();
