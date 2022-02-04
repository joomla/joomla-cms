/**
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

window.insertReadmore = function(editor) {
	"use strict";
	if (!Joomla.getOptions('xtd-readmore')) {
		// Something went wrong!
		return false;
	}

	var content, options = window.Joomla.getOptions('xtd-readmore');

	if (window.Joomla && window.Joomla.editors && window.Joomla.editors.instances && window.Joomla.editors.instances.hasOwnProperty(editor)) {
		content = window.Joomla.editors.instances[editor].getValue();
	} else {
		content = (new Function('return ' + options.editor))();
	}

	if (content.match(/<hr\s+id=("|')system-readmore("|')\s*\/*>/i)) {
		alert(options.exists);
		return false;
	} else {
		/** Use the API, if editor supports it **/
		if (window.Joomla && window.Joomla.editors && window.Joomla.editors.instances && window.Joomla.editors.instances.hasOwnProperty(editor)) {
			window.Joomla.editors.instances[editor].replaceSelection('<hr id="system-readmore" />');
		} else {
			window.jInsertEditorText('<hr id="system-readmore" />', editor);
		}
	}
};
