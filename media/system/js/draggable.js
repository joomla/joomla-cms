/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
document.addEventListener('DOMContentLoaded', function() {
	"use strict";

	/** The container where the draggable will be enabled **/
	var url, formSelector, direction, isNested, container = document.querySelector('.js-draggable'),
	    orderRows = container.querySelectorAll('[name="order[]"]');

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
		/** Add data order attribute for initial ordering */
		for (var i= 0, l = orderRows.length; l > i; i++) {
			orderRows[i].setAttribute('data-order', i+1);
		}

		/** IOS 10 BUG **/
		document.addEventListener("touchstart", function() {},false);

		/** Method to reorder an array
		 *  Not used right now
		 */
		Array.prototype.move = function (from, to) {
			this.splice(to, 0, this.splice(from, 1)[0]);
		};

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
				result.push("cid[]=" + encodeURIComponent(inputRows[i].value));
			}

			return result;
		};

		dragula(
			[container],
			{
				/** Y axis is considered when determining where an element would be dropped **/
				direction: 'vertical',
				/** elements are moved by default, not copied **/
				copy: false,
				/** elements in copy-source containers can be reordered **/
				//copySortSource: true,
				/** spilling will put the element back where it was dragged from, if this is true **/
				revertOnSpill: true,
				/** spilling will `.remove` the element, if this is true **/
				//removeOnSpill: false,

				accepts: function (el, target, source, sibling) {
					if (isNested) {
						if (sibling !== null) {
							return  sibling.getAttribute('data-dragable-group') && sibling.getAttribute('data-dragable-group') == el.getAttribute('data-dragable-group');
						} else {
							return sibling === null || (sibling && sibling.tagName.toLowerCase() === 'tr');
						}
					} else {
						return sibling === null || (sibling && sibling.tagName.toLowerCase() === 'tr');
					}
				}
			}
		).on('drag',
			function(el, source) {}
		).on('cloned',
			function(clone, original) {
				var el = document.querySelector('.gu-mirror');
				el.classList.add('table');
			}
		).on('drop',
			function() {

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
			}
		).on('dragend',
			function(el) {
				var orderRows = container.querySelectorAll('[name="order[]"]');
				/** Reset data order attribute for initial ordering */
				for (var i= 0, l = orderRows.length; l > i; i++) {
					orderRows[i].setAttribute('data-order', i+1);
				}
			}
		);
	}
});
