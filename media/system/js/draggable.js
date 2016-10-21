/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

"use strict";

document.addEventListener('DOMContentLoaded', function() {
	if (Joomla.getOptions('draggable-list')) {

		var options = Joomla.getOptions('draggable-list');
console.log(options.direction)
		// IOS 10 BUG
		document.addEventListener("touchstart", function() {},false);

		// This is here to make the transition to new forms easier.
		// Should be removed when everybody' using the default class!
		if (!document.querySelector(options.id).classList.contains('js-draggable')) {
			document.querySelector(options.id).classList.add('js-draggable');
		}

		// The container where the draggable will be enabled
		var container = document.querySelector('.js-draggable');

		if (container) {
			var saveOrderingUrl = options.url,
				formId = options.formId,
				sortableTable = dragula(
					[container],
					{
						direction: 'vertical',               // Y axis is considered when determining where an element would be dropped
						copy: false,                         // elements are moved by default, not copied
						copySortSource: false,               // elements in copy-source containers can be reordered
						revertOnSpill: true,                 // spilling will put the element back where it was dragged from, if this is true
						removeOnSpill: false                 // spilling will `.remove` the element, if this is true
						// mirrorContainer: document.body,    // set the element that gets mirror elements appended
						// ignoreInputTextSelection: true     // allows users to select input text, see details below
					}
				);

			var sortedArray = function (container, direction) {
				var result = [], orderRows = container.querySelectorAll('input[name="order[]"]');

				if (direction  === 'desc') {
					// Reverse the array
					var orderRows = Array.prototype.slice.call(orderRows);
					orderRows.reverse();
				}

				for (var i= 0, l = orderRows.length; l > i; i++) {
					orderRows[i].value = i;
					result.push("order[]=" + encodeURIComponent(i));
				}
				return result;
			};

			var originalIds = function (form, result) {
				var i, l, inputs = form.querySelectorAll('[name="cid[]"]');

				for(i = 0, l = inputs.length; l>i; i++) {
					 result.push("cid[]=" + encodeURIComponent(inputs[i].value));
				}
				return result;
			};

			sortableTable.on('drop', function(event) {
				if (saveOrderingUrl) {
					// Set the form
					var form  = document.querySelector('#' + formId);

					// Detach task field if exists
					var task = document.querySelector('[name="task"]');

					// Detach task field if exists
					if (task) {
						task.setAttribute('name', 'some__Temporary__Name__');
					}

					var results = sortedArray(container, options.direction);
					results = originalIds(form, results);

					// Prepare the options
					var ajaxOptions = {
						url:    saveOrderingUrl,
						method: 'POST',
						data:    results.join("&"),
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
	}
});
