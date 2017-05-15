/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This is used by tinyMCE in order to allow both mootools and bootstrap modals
 * to close correctly, more tinyMCE related functionality maybe added in the future
 *
 * @package     Joomla
 * @since       3.5.1
 * @version     1.0
 */
Joomla = window.Joomla || {};
Joomla.UI = Joomla.UI || {};
Joomla.UI.Modal = Joomla.UI.Modal || {};

document.addEventListener('DOMContentLoaded', function () {
	if (typeof window.jModalClose_no_tinyMCE === 'undefined')
	{
		window.jModalClose_no_tinyMCE = typeof(Joomla.UI.Modal.close) == 'function'  ?  Joomla.UI.Modal.close  :  false;

		Joomla.UI.Modal.close = function () {
			if (window.jModalClose_no_tinyMCE) window.jModalClose_no_tinyMCE.apply(this, arguments);
			tinyMCE.activeEditor.windowManager.close();
		};
	}
});
