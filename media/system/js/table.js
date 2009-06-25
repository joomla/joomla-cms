/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JTable javascript behavior
 *
 * @package		Joomla
 */

var JTable = new Class( {

	getOptions : function() {
		return {
		};
	},

	initialize : function(table, options) {
		this.setOptions(this.getOptions(), options);
		this.boxes = table.getElements('input[type=checkbox]');
		this.boxes.addEvent('click', this.doselect.bindWithEvent(this));
	},
	
	doselect: function(e){
		e = new Event(e);
		var current = $(e.target);
		if(e.shift && $type(this.last) !== false){
			var checked = current.getProperty('checked')  ? 'checked' : '';
			var range = [this.boxes.indexOf(current), this.boxes.indexOf(this.last)].sort();
			for(var i=range[0]; i <= range[1]; i++){
				this.boxes[i].setProperty('checked', checked);
			}
		}
		this.last = current; 
	}
});

JTable.implement(new Events);
JTable.implement(new Options);
