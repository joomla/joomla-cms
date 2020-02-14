/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

document.addEventListener('DOMContentLoaded', function() {
	"use strict";

	/** Get the elements **/
	var modulesLinks = document.querySelectorAll('.js-module-insert'), i,
		positionsLinks = document.querySelectorAll('.js-position-insert');

	/** Assign listener for click event (for single module id insertion) **/
	for (i= 0; modulesLinks.length > i; i++) {
		modulesLinks[i].addEventListener('click', function(event) {
			event.preventDefault();
			var modid = event.target.getAttribute('data-module'),
				editor = event.target.getAttribute('data-editor');

			/** Use the API, if editor supports it **/
			if (window.parent.Joomla && window.parent.Joomla.editors && window.parent.Joomla.editors.instances && window.parent.Joomla.editors.instances.hasOwnProperty(editor)) {
				window.parent.Joomla.editors.instances[editor].replaceSelection("{loadmoduleid " + modid + "}")
			} else {
				window.parent.jInsertEditorText("{loadmoduleid " + modid + "}", editor);
			}

			window.parent.jModalClose();
		});
	}

	/** Assign listener for click event (for position insertion) **/
	for (i= 0; positionsLinks.length > i; i++) {
		positionsLinks[i].addEventListener('click', function(event) {
			event.preventDefault();
			var position = event.target.getAttribute('data-position'),
				editor = event.target.getAttribute('data-editor');

			/** Use the API, if editor supports it **/
			if (window.Joomla && window.Joomla.editors && Joomla.editors.instances && Joomla.editors.instances.hasOwnProperty(editor)) {
				Joomla.editors.instances[editor].replaceSelection("{loadposition " + position + "}")
			} else {
				window.parent.jInsertEditorText("{loadposition " + position + "}", editor);
			}

			window.parent.jModalClose();
		});
	}

});
