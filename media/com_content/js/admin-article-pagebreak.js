/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

document.addEventListener('DOMContentLoaded', function() {
	"use strict";
	/** Get the button **/
	var button = document.querySelector('.js-insert-pagebreak'),
		insertPagebreak = function() {
			/** Get the pagebreak title **/
			var title = document.getElementById('title').value;

			if (title != '') {
				title = 'title="' + title + '"';
			}

			/** Get the pagebreak toc alias -- not inserting for now **/
			/** don't know which attribute to use... **/
			var alt = document.getElementById('alt').value;
			if (alt != '') {
				alt = 'alt="' + alt + '"';
			}

			var tag = '<hr class="system-pagebreak" ' + title + ' ' + alt + '/>';
			window.parent.jInsertEditorText(tag, event.target.getAttribute('data-editor'));
			window.parent.jModalClose();
			return false;
		};

	button.addEventListener('click', function(event) {
		event.preventDefault();
		insertPagebreak();
	});
});
