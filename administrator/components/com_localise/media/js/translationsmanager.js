/**
 * JS for Translations Component
 * @copyright Copyright (C) 2007 Ifan Evans. All rights reserved.
 * @license GNU/GPL
 */

/**
 * ffAutoCorrect
 * Dynamically change user input in a form field by checking the last characters of the field
 * against a list of keys held in 'ffacList' and replacing them with the corresponding value
 * eg: ffacList[a^]=â
 * there's a one second delay on the replacement
 * set ffacList outside the function
 * call the function using onkeyup="ffAutoCorrect(this)"
 */

var ffacElement;
var ffacList = new Array();
var ffacOldName = '';
var ffacOldValue = '';
function ffAutoCorrect(element) {
	// initialise variables on first call, then timeout for one second
	if (typeof(element) == 'object') {
		ffacElement = element;
		element = null;
		ffacOldName = ffacElement.name;
		ffacOldValue = ffacElement.value;
		setTimeout("ffAutoCorrect()",1000);
	}
	// process on second call, only if name and value are unchanged
	else if ( (ffacElement.name == ffacOldName) && (ffacElement.value == ffacOldValue) ) {
		// get element length
		el = ffacElement.value.length;
		// process the AutoCorrect List
		for (s in ffacList) {
			// skip non-strings
			if ( typeof(ffacList[s]) != "string" ) continue;
			// get search string length
			sl = s.length;
			// check element is at least as long as search string
			if (el>=sl) {
				// check for matching string at end of element
				if ( ffacElement.value.slice(el-sl) == s ) {
					// replace matching string
					ffacElement.value = ffacElement.value.slice(0,el-sl) + ffacList[s];
					// return after making the replacement
					return;
				}
			}
		}
	}
}

/**
 * ffAppendRow
 * Append a row (src) to the end of a table (table)
 */
function ffAppendRow(table,src) {
	if ( document.getElementById(table) && document.getElementById(src) ) {
		// add new row at end of table
		var newTR = document.getElementById(table).insertRow(-1);
		// IE won't let us set the innerHTML of a row object, we need to copy the cells and their properties from the source
		var cells = document.getElementById(src).cells;
		var props = new Array('width','align','valign','colSpan','innerHTML','className');
		for(var td=0;td<cells.length;td++){
			// add new cell at the end of the row, then copy the properties
			var newTD = newTR.insertCell(-1);
			for (var p=0;p<props.length;p++) {
				var prop = props[p];
				if (cells[td][prop]) newTD[prop] = cells[td][prop];
			}
		}
	}
}

/**
 * ffCheckDisable
 * Disable the fields linked to a checkbox (chk) by ID (id)
 */
var ffchkconfirm = true;
var ffchkmessage = 'Are you sure you want to delete this phrase?';
function ffCheckDisable(chk,id) {
	if ((!chk) || (!id)) return;
	// 1: box has been checked - turn off flag
	// 2: box has been cleared - flag is on
	if (! chk.checked) {
		ffchkconfirm = true;
	} else if (ffchkconfirm) {
		chk.checked = ffchkconfirm = window.confirm(ffchkmessage);
	}
	// set the key and input form
	chk.form['key'+id].disabled = chk.form['value'+id].disabled = chk.checked;
}
/**
 * ffCopySpanToInput
 * Copy the reference value to an input box
 */
function ffCopyRef2Val(i) {
	src = 'ref' + i;
	dst = 'value' + i;
	if ( document.getElementById(src) && document.getElementById(dst) ) {
		document.getElementById(dst).value = document.getElementById(src).innerHTML;
	}
}
/**
 * ffReset
 * Reset the value of a form field to its default value
 */
function ffResetVal(id) {
	if ( document.getElementById(id) ) {
		document.getElementById(id).value = document.getElementById(id).defaultValue;
	}
}
