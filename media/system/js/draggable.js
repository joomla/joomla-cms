/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

"use strict";

document.addEventListener('DOMContentLoaded', function() {

	// The container where the draggable will be enabled
	var url, formSelector, direction, container = document.querySelector('.js-draggable');

	if (container) {
		// The script expects a form with a class js-form
		// A table with the tbody with a class js-draggable
		//                        with a data-url with the ajax request end point and
		//                        with a data-direction for asc/desc
		url = container.getAttribute('data-url');
		direction = container.getAttribute('data-direction');
		formSelector = '.js-form';
	} else if (Joomla.getOptions('draggable-list')) {

		var options = Joomla.getOptions('draggable-list');

		container = document.querySelector(options.id);
		// This is here to make the transition to new forms easier.
		// Should be removed when everybody' using the default class!
		if (!container.classList.contains('js-draggable')) {
			container.classList.add('js-draggable');
		}

		url = options.url;
		formSelector = options.formId;
		direction = options.direction;
	}

	if (container) {
		// IOS 10 BUG
		document.addEventListener("touchstart", function() {},false);

		var draggableTable = dragula(
				[container],
				{
					direction: 'vertical',               // Y axis is considered when determining where an element would be dropped
					copy: false,                         // elements are moved by default, not copied
					copySortSource: false,               // elements in copy-source containers can be reordered
					revertOnSpill: true,                 // spilling will put the element back where it was dragged from, if this is true
					removeOnSpill: false                 // spilling will `.remove` the element, if this is true
				}
			);

		var getOrderData = function (container, direction) {
			var i, l, result = [],
				orderRows = container.querySelectorAll('input[name="order[]"]'),
				inputRows = container.querySelectorAll('[name="cid[]"]');

			if (direction  === 'desc') {
				// Reverse the array
				orderRows = Array.prototype.slice.call(orderRows);
				inputRows = Array.prototype.slice.call(inputRows);
				orderRows.reverse();
				inputRows.reverse();
			}

			// Get the order array
			for (i= 0, l = orderRows.length; l > i; i++) {
				orderRows[i].value = i;
				result.push("order[]=" + encodeURIComponent(i));
			}

			// Get the id array
			for(i = 0, l = inputRows.length; l>i; i++) {
				result.push("cid[]=" + encodeURIComponent(inputRows[i].value));
			}

			return result;
		};

		draggableTable.on('drop', function() {
			if (url) {
				// Detach task field if exists
				var task = document.querySelector('[name="task"]');

				// Detach task field if exists
				if (task) {
					task.setAttribute('name', 'some__Temporary__Name__');
				}

				// Prepare the options
				var ajaxOptions = {
					url:    url,
					method: 'POST',
					data:    getOrderData(container, direction).join("&"),
					perform: true
				};

				Joomla.request(ajaxOptions);

				// Re-Append original task field
				if (task) {
					task.setAttribute('name', 'task');
				}
			}
		});
	}
});
