/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

"use strict";

document.addEventListener('DOMContentLoaded', function() {

	/** The container where the draggable will be enabled **/
	var url, formSelector, direction, isNested, container = document.querySelector('.js-draggable');

	if (container) {
		/** The script expects a form with a class js-form
		 *  A table with the tbody with a class js-draggable
		 *                         with a data-url with the ajax request end point and
		 *                         with a data-direction for asc/desc
		 */
		url = container.getAttribute('data-url');
		direction = container.getAttribute('data-direction');
		isNested = container.getAttribute('data-nested');
	} else if (Joomla.getOptions('draggable-list')) {

		var options = Joomla.getOptions('draggable-list');

		container = document.querySelector(options.id);
		/**
		 * This is here to make the transition to new forms easier.
		 */
		if (!container.classList.contains('js-draggable')) {
			container.classList.add('js-draggable');
		}

		url = options.url;
		direction = options.direction;
		isNested = options.nested;
	}

	if (container) {
		/** IOS 10 BUG **/
		document.addEventListener("touchstart", function() {},false);

		var draggableTable = dragula(
			[container],
			{
				/** Y axis is considered when determining where an element would be dropped **/
				direction: 'vertical',
				/** elements are moved by default, not copied **/
				copy: false,
				/** elements in copy-source containers can be reordered **/
				copySortSource: true,
				/** spilling will put the element back where it was dragged from, if this is true **/
				revertOnSpill: true,
				/** spilling will `.remove` the element, if this is true **/
				removeOnSpill: false
			}
		);

		var getOrderData = function (container, direction) {
			var i, l, result = [],
				orderRows = container.querySelectorAll('[name="order[]"]'),
				inputRows = container.querySelectorAll('[name="cid[]"]');

			if (direction  === 'desc') {
				/** Reverse the array **/
				orderRows = Array.prototype.slice.call(orderRows);
				inputRows = Array.prototype.slice.call(inputRows);
				orderRows.reverse();
				inputRows.reverse();
			}

			/** Get the order array **/
			for (i= 0, l = orderRows.length; l > i; i++) {
				orderRows[i].value = i;
				result.push("order[]=" + encodeURIComponent(i));
			}

			/** Get the id array **/
			for(i = 0, l = inputRows.length; l>i; i++) {
				result.push("cid[]=" + encodeURIComponent(inputRows[i].value));
			}

			return result;
		};

		/** Disable any elements that do not belong in the same group **/
		draggableTable.on('drag', function(el, source) {
			if (isNested) {
				var rows = source.getElementsByTagName('tr');
				for (var i = 0, l = rows.length; l>i; i++) {
					if (parseInt(el.getAttribute('data-dragable-group')) !== parseInt(rows[i].getAttribute('data-dragable-group'))) {
						rows[i].style.display = 'none';
						rows[i].querySelector('[name="cid[]"]').setAttribute('name', 'input_TEMP_rename__');
						rows[i].querySelector('[name="order[]"]').setAttribute('name', 'order_TEMP_rename__');
					}
				}
			}
		});

		/** Alter the class of the shadow element **/
		draggableTable.on('cloned', function(clone, original) {
			var el = document.querySelector('.gu-mirror');
			el.classList.add('table');
		});

		/** The logic for the drop event **/
		draggableTable.on('drop', function() {
			if (url) {
				/** Detach task field if exists **/
				var task = document.querySelector('[name="task"]');

				/** Detach task field if exists **/
				if (task) {
					task.setAttribute('name', 'some__Temporary__Name__');
				}

				/** Prepare the options **/
				var ajaxOptions = {
					url:    url,
					method: 'POST',
					data:    getOrderData(container, direction).join("&"),
					perform: true
				};

				Joomla.request(ajaxOptions);

				/** Re-Append original task field **/
				if (task) {
					task.setAttribute('name', 'task');
				}
			}
		});

		/** Restore any elements that have been altered **/
		draggableTable.on('dragend', function(el) {
			if (isNested) {
				var rows = container.querySelectorAll('tr');

				for (var i = 0, l = rows.length; l > i; i++) {
					if (rows[i].style.display === 'none') {
						rows[i].style.display = '';
						rows[i].querySelector('[name="input_TEMP_rename__"]').setAttribute('name', 'cid[]');
						rows[i].querySelector('[name="order_TEMP_rename__"]').setAttribute('name', 'order[]');
					}
				}
			}
		});
	}
});
