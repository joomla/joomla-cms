/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow shift select in administrator grids
 */
(function() {
	Joomla = Joomla || {};

	Joomla.JMultiSelect = new Class({
		initialize : function(table) {
			this.table = document.id(table);
			if (this.table) {
				this.boxes = this.table.getElements('input[type=checkbox]');
				this.boxes.addEvent('click', function(e){
					this.doselect(e);
				}.bind(this));
			}
		},

		doselect: function(e) {
			var current = document.id(e.target);
			if (e.shift && typeOf(this.last) !== 'null') {
				var checked = current.getProperty('checked') ? 'checked' : '';
				var range = [this.boxes.indexOf(current), this.boxes.indexOf(this.last)].sort(function(a, b) {
					//Shorthand to make sort() sort numerical instead of lexicographic
					return a-b;
				});
				for (var i=range[0]; i <= range[1]; i++) {
					this.boxes[i].setProperty('checked', checked);
				}
			}
			this.last = current;
		}
	});
})();
