/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {

		// Handle toggle all
		[].slice.call(document.querySelectorAll('.filter-toggle-all')).forEach(function(button) {
			button.addEventListener('click', function(e) {
				[].slice.call(document.querySelectorAll('.filter-node')).forEach(function(node) {
					node.click();
				});
			});
		});

		// Update the count
		[].slice.call(document.querySelectorAll('.filter-node')).forEach(function(node) {
			var count = document.getElementById('jform_map_count');
			if (count) {
				count.value = document.querySelectorAll('input[type="checkbox"]:checked').length;
			}
		});

		// Expand/collapse
		var expandAccordion = document.getElementById('expandAccordion');
		if (expandAccordion) {
			expandAccordion.addEventListener('click', function(event) {
				event.preventDefault();

				if (event.target.innerText == Joomla.JText._('COM_FINDER_FILTER_SHOW_ALL')) {
					event.target.innerText = Joomla.JText._('COM_FINDER_FILTER_HIDE_ALL');

					jQuery('.collapse:not(.in)').each(function() {
						jQuery(this).collapse('toggle');
					});
				}
				else {
					event.target.innerText = Joomla.JText._('COM_FINDER_FILTER_SHOW_ALL');

					jQuery('.collapse.in').each(function() {
						jQuery(this).collapse('toggle');
					});
				}
			});
		}

	});

})();
