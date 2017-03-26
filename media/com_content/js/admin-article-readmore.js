/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

window.insertReadmore = function(editor) {
	"use strict";
	if (!Joomla.getOptions('xtd-readmore')) {
		// Something went wrong!
		return false;
	}

	var options, content;

	options = window.parent.Joomla.getOptions('xtd-readmore');

	content = (new Function('return ' + options.editor))();

	if (content.match(/<hr\s+id=("|')system-readmore("|')\s*\/*>/i)) {
		alert(options.exists);
		return false;
	} else {
		/** Use the API, if editor supports it **/
		if (window.parent.Joomla && window.parent.Joomla.editors && window.parent.Joomla.editors.instances && window.parent.Joomla.editors.instances.hasOwnProperty(editor)) {
			window.parent.Joomla.editors.instances[editor].replaceSelection('<hr id="system-readmore" />')
		} else {
			window.parent.jInsertEditorText('<hr id="system-readmore" />', editor);
		}
	}
};
