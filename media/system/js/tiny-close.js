/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This is used by tinyMCE in order to allow both mootools and bootstrap modals
 * to close correctly
 *
 * @package     Joomla
 * @since       3.6
 * @version     1.0
 */
document.onreadystatechange = function () {
	if (document.readyState == "interactive") {
		if (jModalClose_tinyMCE_added === undefined) {
			var __tmp = jModalClose !== undefined && typeof(jModalClose) == 'function' ? jModalClose : false;

			jModalClose = function () {
				if (__tmp)  __tmp.apply(this, arguments);
				tinyMCE.activeEditor.windowManager.close();
			};

			window.jModalClose_tinyMCE_added = 1;
		}

		if (SqueezeBox_tinyMCE_added === undefined) {
			var __tmp = SqueezeBox !== undefined ? SqueezeBox.close : false;
			if (SqueezeBox === undefined)  SqueezeBox = {};

			SqueezeBox.close = function () {
				if (__tmp)  __tmp.apply(this, arguments);
				tinyMCE.activeEditor.windowManager.close();
			};

			window.SqueezeBox_tinyMCE_added = 1;
		}
	}
};
