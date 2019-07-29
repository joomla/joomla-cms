/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Editor None
 */
;(function(Joomla, window, document){

	function insertAtCursor(myField, myValue) {
		if (document.selection) {
			// IE support
			myField.focus();
			var sel = document.selection.createRange();
			sel.text = myValue;
		} else if (myField.selectionStart || myField.selectionStart === 0) {
			// MOZILLA/NETSCAPE support
			myField.value = myField.value.substring(0, myField.selectionStart)
				+ myValue
				+ myField.value.substring(myField.selectionEnd, myField.value.length);
		} else {
			myField.value += myValue;
		}
	}

	function getSelection(myField) {
		if (document.selection) {
			// IE support
			myField.focus();
			return document.selection.createRange();
		} else if (myField.selectionStart || myField.selectionStart === 0) {
			// MOZILLA/NETSCAPE support
			return myField.value.substring(myField.selectionStart, myField.selectionEnd);
		} else {
			return myField.value;
		}
	}

	// @deprecated 4.0 Use directly Joomla.editors.instances[editor].replaceSelection(text);
	window.jInsertEditorText = function(text, editor) {
		Joomla.editors.instances[editor].replaceSelection(text);
	};

	document.addEventListener('DOMContentLoaded', function() {
		var editors = document.querySelectorAll('.js-editor-none');

		for(var i = 0, l = editors.length; i < l; i++) {
			/** Register Editor */
			Joomla.editors.instances[editors[i].childNodes[0].id] = {
				'id': editors[i].childNodes[0].id,
				'element':  editors[i].childNodes[0],
				'getValue': function () { return this.element.value; },
				'setValue': function (text) { return this.element.value = text; },
				'getSelection': function () { return getSelection(this.element); },
				'replaceSelection': function (text) { return insertAtCursor(this.element, text); },
				'onSave': function() { return ''; }
			};
		}
	});
}(Joomla, window, document));
