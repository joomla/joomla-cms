/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function(Joomla, document) {
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
	 * @since 4.0.0
	 */
	Joomla.popupWindow = function(mypage, myname, w, h, scroll) {
		var winl = (screen.width - w) / 2,
		    wint = (screen.height - h) / 2,
		    winprops = 'height=' + h +
			    ',width=' + w +
			    ',top=' + wint +
			    ',left=' + winl +
			    ',scrollbars=' + scroll +
			    ',resizable';

		window.open(mypage, myname, winprops).window.focus();
	};

	Joomla.processScrollInit = function() {
		var subhead = document.getElementById('subhead');
		var wrapper = document.getElementById('wrapper');

		if (subhead) {
			// Fix toolbar and footer width for edit views
			if (wrapper.classList.contains('wrapper0')) {
				subhead.style.left = 0;
			}

			navTop = document.querySelector('.subhead').offsetHeight;

			// Only apply the scrollspy when the toolbar is not collapsed
			if (document.body.clientWidth > 480) {
				document.querySelector('.subhead-collapse').style.height = document.querySelector('.subhead').style.height;
				subhead.style.width = 'auto';
			}
		}
	}

	Joomla.processScroll = function() {
		var subhead = document.getElementById('subhead');

		if (subhead) {
			var scrollTop = (window.pageYOffset || subhead.scrollTop)  - (subhead.clientTop || 0);

			if (scrollTop >= navTop && !isFixed) {
				isFixed = true;
				subhead.classList.add('subhead-fixed');
			} else if (scrollTop <= navTop && isFixed) {
				isFixed = false;
				subhead.classList.remove('subhead-fixed');
			}
		}
	}

	var navTop;
	var isFixed = false;

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

		/**
		 * Sticky Toolbar
		 */
		Joomla.processScrollInit();
		Joomla.processScroll();

		document.addEventListener('resize', Joomla.processScrollInit, false);
		document.addEventListener('scroll', Joomla.processScroll);

	});
})(Joomla, document);
