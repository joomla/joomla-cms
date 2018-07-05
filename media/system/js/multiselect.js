/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow shift select in administrator grids
 */
Joomla = window.Joomla || {};

(function(Joomla) {
	Joomla.JMultiSelect = function(formElement) {
		'use strict';

		var last, boxes,

		    initialize = function(formElement) {
			    var tableEl = document.querySelector(formElement);

			    if (tableEl) {
				    boxes = tableEl.querySelectorAll('input[type=checkbox]');
				    var i = 0, countB = boxes.length;
				    for (i; boxes < countB; i++) {
					    boxes[i].addEventListener('click', function (e) {
						    doselect(e)
					    });
				    }
			    }
		    },

		    doselect = function(e) {
			    var current = e.target, isChecked, lastIndex, currentIndex, swap;
			    if (e.shiftKey && last.length) {
				    isChecked = current.hasAttribute(':checked');
				    lastIndex = boxes.index(last);
				    currentIndex = boxes.index(current);
				    if (currentIndex < lastIndex) {
					    // handle selection from bottom up
					    swap = lastIndex;
					    lastIndex = currentIndex;
					    currentIndex = swap;
				    }
				    boxes.slice(lastIndex, currentIndex + 1).setAttribute('checked', isChecked);
			    }

			    last = current;
		    };
		initialize(formElement);
	};

	document.addEventListener('DOMContentLoaded', function(event) {
		'use strict';

		if (Joomla.getOptions && typeof Joomla.getOptions === 'function' && Joomla.getOptions('js-multiselect')) {
			if (Joomla.getOptions('js-multiselect').formName) {
				Joomla.JMultiSelect(Joomla.getOptions('js-multiselect').formName);
			} else {
				Joomla.JMultiSelect('adminForm');
			}
		}

		var rows = document.querySelectorAll('tr[class^="row"]');

		// Changes the background-color on every <td> inside a <tr>
		function changeBg(item, checkall) {
			// Check if it should add or remove the background colour
			if (checkall.checked) {
				item.querySelectorAll('td').forEach (function(td) {
					td.classList.add('row-selected');
				});
			}
			else {
				item.querySelectorAll('td').forEach (function(td) {
					td.classList.remove('row-selected');
				});
			}
		}

		var checkallToggle = document.getElementsByName('checkall-toggle')[0];

		if (checkallToggle) {
			checkallToggle.addEventListener('click', function(event) {
				var checkall = this;

				rows.forEach(function(row, index) {
					changeBg(row, checkall);
				});
			});
		}

		if (rows.length) {
			rows.forEach(function(row, index) {
				row.addEventListener('click', function(event) {
					var clicked   = 'cb' + index, cbClicked = document.getElementById(clicked);

					if (cbClicked) {
						if (!(event.target.id == clicked)) {
							cbClicked.checked = !cbClicked.checked;
							Joomla.isChecked(cbClicked.checked);
						}

						changeBg(this, cbClicked);
					}
				});
			});
		}
	});
})(Joomla);
