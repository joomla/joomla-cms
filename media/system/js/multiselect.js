/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow shift select in administrator grids
 */
Joomla = window.Joomla || {};

Joomla.JMultiSelect = function(table) {
	"use strict";

	var last, boxes,

		initialize = function(table) {
			var tableEl = document.querySelector(table);

			if (tableEl) {
				boxes = tableEl.querySelectorAll('input[type=checkbox]');
				var i = 0, countB = boxes.length;
				for (i; boxes<countB; i++) {
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
	initialize(table);
};

document.addEventListener("DOMContentLoaded", function(event) { 
	color = '#d9edf7';
	rows   = document.querySelectorAll('tr[class^="row"]');

	// Changes the background-color on every <td> inside a <tr>
	function changeBg(item, color) {
		item.querySelectorAll('td').forEach (function(td) {
			td.style.backgroundColor = color;
		}); 
	}

	document.getElementsByName("checkall-toggle")[0].addEventListener("click", function(event) {
		if (this.checked) {
			rows.forEach(function(row, index) {
				changeBg(row, color);
			});
		}
		else {
			rows.forEach(function(row, index) {
				changeBg(row, '');
			});
		}
	});

	rows.forEach(function(row, index) {
		row.addEventListener("click", function(event) {
			clicked   = 'cb' + index;
			cbClicked = document.getElementById(clicked);

			if (!(event.target.id == clicked)) {
				cbClicked.checked = !cbClicked.checked;
				Joomla.isChecked(cbClicked.checked);
			}
	
			if (cbClicked.checked) {
				changeBg(this, color);
			}
			else {
				changeBg(this, '');
			}
		});
	});
});

