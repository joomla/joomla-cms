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

// Changes the background-color on every <td> inside a <tr>
Joomla.changeRowListBg = function(item, checkall) {

	// Check if it should add or remove the background colour
	if (checkall.checked) {
		item.querySelectorAll('td').forEach (function(td) {
			td.classList.add('row-selected');
		});
	} else {
		item.querySelectorAll('td').forEach (function(td) {
			td.classList.remove('row-selected');
		});
	}
};

/**
 * USED IN: administrator/components/com_cache/views/cache/tmpl/default.php
 * administrator/components/com_installer/views/discover/tmpl/default_item.php
 * administrator/components/com_installer/views/update/tmpl/default_item.php
 * administrator/components/com_languages/helpers/html/languages.php
 * libraries/joomla/html/html/grid.php
 *
 * @param isitchecked
 * @param form
 * @return
 */
Joomla.isChecked = function( isitchecked, form ) {
	if ( typeof form  === 'undefined' ) {
		form = document.getElementById( 'adminForm' );
	}

	form.boxchecked.value = isitchecked ? parseInt(form.boxchecked.value) + 1 : parseInt(form.boxchecked.value) - 1;

	// If we don't have a checkall-toggle, done.
	if ( !form.elements[ 'checkall-toggle' ] ) return;

	// Toggle main toggle checkbox depending on checkbox selection
	var c = true,
	    i, e, n;

	for ( i = 0, n = form.elements.length; i < n; i++ ) {
		e = form.elements[ i ];

		if ( e.type == 'checkbox' && e.name != 'checkall-toggle' && !e.checked ) {
			c = false;
			break;
		}
	}

	form.elements[ 'checkall-toggle' ].checked = c;
};

/**
 * USED IN: all list forms.
 *
 * Toggles the check state of a group of boxes
 *
 * Checkboxes must have an id attribute in the form cb0, cb1...
 *
 * @param   mixed   The number of box to 'check', for a checkbox element
 * @param   string  An alternative field name
 */
Joomla.checkAll = function( checkbox, stub ) {
	if (!checkbox.form) return false;

	stub = stub ? stub : 'cb';

	var c = 0,
	    i, e, n;

	for ( i = 0, n = checkbox.form.elements.length; i < n; i++ ) {
		e = checkbox.form.elements[ i ];

		if ( e.type == checkbox.type && e.id.indexOf( stub ) === 0 ) {
			e.checked = checkbox.checked;
			c += e.checked ? 1 : 0;
		}
	}

	if ( checkbox.form.boxchecked ) {
		checkbox.form.boxchecked.value = c;
	}

	return true;
};

// needed for Table Column ordering
/**
 * USED IN: libraries/joomla/html/html/grid.php
 * There's a better way to do this now, can we try to kill it?
 */
Joomla.saveorder = function ( n, task ) {
	Joomla.checkAll_button( n, task );
};

/**
 * Checks all the boxes unless one is missing then it assumes it's checked out.
 * Weird. Probably only used by ^saveorder
 *
 * @param   integer  n     The total number of checkboxes expected
 * @param   string   task  The task to perform
 *
 * @return  void
 */
Joomla.checkAll_button = function ( n, task ) {
	task = task ? task : 'saveorder';

	var j, box;

	for ( j = 0; j <= n; j++ ) {
		box = document.adminForm[ 'cb' + j ];

		if ( box ) {
			box.checked = true;
		} else {
			alert( "You cannot change the order of items, as an item in the list is `Checked Out`" );
			return;
		}
	}

	Joomla.submitform( task );
};

/**
 * USED IN: libraries/joomla/html/html/grid.php
 * In other words, on any reorderable table
 */
Joomla.tableOrdering = function( order, dir, task, form ) {
	if ( typeof form  === 'undefined' ) {
		form = document.getElementById( 'adminForm' );
	}

	form.filter_order.value = order;
	form.filter_order_Dir.value = dir;
	Joomla.submitform( task, form );
};

document.addEventListener('DOMContentLoaded', function() {
	'use strict';

	var rows = [].slice.call(document.querySelectorAll('tr[class^="row"]'));

	if (rows.length) {
		document.getElementsByName('checkall-toggle')[0].addEventListener('click', function() {
			var checkall = this;

			rows.forEach(function(row) {
				Joomla.changeRowListBg(row, checkall);
			});
		});

		rows.forEach(function(row, index) {
			row.addEventListener('click', function(event) {
				var clicked   = 'cb' + index, cbClicked = document.getElementById(clicked);

				if (!(event.target.id == clicked)) {
					cbClicked.checked = !cbClicked.checked;
					Joomla.isChecked(cbClicked.checked);
				}

				Joomla.changeRowListBg(this, cbClicked);
			});
		});
	}


		var actions = [].slice.call(document.querySelectorAll('a.move_up, a.move_down, a.grid_true, a.grid_false, a.grid_trash'));

		if (actions.length) {
			actions.each(function(action){
				action.addEventListener('click', function(event){
					var args = JSON.decode(event.target.getAttribute(rel));
					Joomla.listItemTask(args.id, args.task);
				});
			});
			[].slice.call(document.querySelectorAll('input.check-all-toggle')).each(function(item){
				item.addEventListener('click', function(event){
					if (event.target.checked) {
						[].slice.call(event.target.form.querySelectorAll('input[type="checkbox"]')).each(function(item){
							item.checked = true;
						})
					}
					else {
						[].slice.call(event.target.form.querySelectorAll('input[type="checkbox"]')).each(function(item){
							item.checked = false;
						})
					}
				});
			});
		}
});

