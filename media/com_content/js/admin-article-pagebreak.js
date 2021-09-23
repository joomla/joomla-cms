/**
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {
	"use strict";

	window.insertPagebreak = function(editor) {
		/** Get the pagebreak title **/
		var alt, tag, title = document.getElementById('title').value;

		if (!window.parent.Joomla.getOptions('xtd-pagebreak')) {
			// Something went wrong!
			window.parent.jModalClose();
			return false;
		}

		/** Get the pagebreak toc alias -- not inserting for now **/
		/** don't know which attribute to use... **/
		alt = document.getElementById('alt').value;

		title  = (title != '') ? 'title="' + title + '"' : '';
		alt    = (alt != '') ? 'alt="' + alt + '"' : '';

		tag = '<hr class="system-pagebreak" ' + title + ' ' + alt + '/>';

		/** Use the API, if editor supports it **/
		if (window.parent.Joomla && window.parent.Joomla.editors && window.parent.Joomla.editors.instances && window.parent.Joomla.editors.instances.hasOwnProperty(editor)) {
			window.parent.Joomla.editors.instances[editor].replaceSelection(tag)
		} else {
			window.parent.jInsertEditorText(tag, editor);
		}

		window.parent.jModalClose();
		return false;
	};
})();
