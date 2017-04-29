/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

window.insertReadmore = function(editor) {
	"use strict";

	var content = Joomla.editors.instances[editor].getValue();

	if (content.match(/<hr\s+id=("|')system-readmore("|')\s*\/*>/i)) {
		alert(Joomla.JText._('PLG_READMORE_ALREADY_EXISTS'));
		return false;
	} else {
		Joomla.editors.instances[editor].replaceSelection('<hr id="system-readmore" />');
	}
};
