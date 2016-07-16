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
jQuery(window).bind("load", function () {
	if (jModalClose === undefined && typeof(jModalClose) != 'function') {
		var jModalClose;
		jModalClose = function () {
			tinyMCE.activeEditor.windowManager.close();
		}
	} else {
		var oldClose = jModalClose;
		jModalClose = function () {
			oldClose.apply(this, arguments);
			tinyMCE.activeEditor.windowManager.close();
		};
	}
	if (SqueezeBox != undefined) {
		var oldSqueezeBox = SqueezeBox.close;
		SqueezeBox.close = function () {
			oldSqueezeBox.apply(this, arguments);
			tinyMCE.activeEditor.windowManager.close();
		}
	} else {
		var SqueezeBox = {};
		SqueezeBox.close = function () {
			tinyMCE.activeEditor.windowManager.close();
		}
	}
});