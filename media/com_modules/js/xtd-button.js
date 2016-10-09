/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

document.addEventListener('DOMContentLoaded', function() {
	"use strict";

	/** Get the elements **/
	var modulesLinks = document.querySelectorAll('.js-module-insert'),
		positionsLinks = document.querySelectorAll('.js-position-insert');

	/** Assign listener for click event (for single module insertion) **/
	for (var i= 0; modulesLinks.length > i; i++) {
		modulesLinks[i].addEventListener('click', function(event) {
			event.preventDefault();
			var type = event.target.getAttribute('data-module'),
				name = event.target.getAttribute('data-title'),
				editor = event.target.getAttribute('data-editor');
			window.parent.jInsertEditorText("{loadmodule " + type + "," + name + "}", editor);
			window.parent.jModalClose();
		});
	}

	/** Assign listener for click event (for position insertion) **/
	for (var j= 0; positionsLinks.length > j; j++) {
		positionsLinks[j].addEventListener('click', function(event) {
			event.preventDefault();
			var position = event.target.getAttribute('data-position'),
				editor = event.target.getAttribute('data-editor');
			window.parent.jInsertEditorText("{loadposition " + position + "}", editor);
			window.parent.jModalClose();
		});
	}
});
