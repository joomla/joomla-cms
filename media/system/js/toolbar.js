/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function(Joomla) {
	'use strict';

	/**
	 * USED IN: libraries/joomla/html/toolbar/button/help.php
	 *
	 * Pops up a new window in the middle of the screen
	 *
	 * @param {string}  mypage  The URL for the redirect
	 * @param {string}  myname  The name of the page
	 * @param {int}     w       The width of the new window
	 * @param {int}     h       The height of the new window
	 * @param {string}  scroll  The vertical/horizontal scroll bars
	 *
	 * @since __DEPLOY_VERSION__
	 */
	Joomla.popupWindow = function( mypage, myname, w, h, scroll ) {
		var winl = ( screen.width - w ) / 2,
		    wint = ( screen.height - h ) / 2,
		    winprops = 'height=' + h +
			    ',width=' + w +
			    ',top=' + wint +
			    ',left=' + winl +
			    ',scrollbars=' + scroll +
			    ',resizable';

		window.open( mypage, myname, winprops ).window.focus();
	};

	document.addEventListener('DOMContentLoaded', function() {
		/**
		 * Fix the alignment of the Options and Help toolbar buttons
		 */
		var toolbarOptions = document.getElementById('toolbar-options'),
		    toolbarHelp = document.getElementById('toolbar-help');

		if (toolbarHelp && !toolbarOptions) {
			toolbarHelp.classList.add('ml-auto');
		}
		if (toolbarOptions && !toolbarHelp) {
			toolbarOptions.classList.add('ml-auto');
		}
	});
})(Joomla);
