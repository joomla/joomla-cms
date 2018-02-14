/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};
 
(function( Joomla, document ) {
	"use strict";

	document.addEventListener('DOMContentLoaded', function() {
		Joomla.toggleContainer = function(name)
		{
			var e = document.getElementById(name);
			e.style.display = (e.style.display == 'none') ? 'block' : 'none';
		};

		var sidebarWrapper = document.getElementById('sidebar-wrapper'),
		    debugWrapper   = document.getElementById('system-debug');
		if (sidebarWrapper && debugWrapper) {
			debugWrapper.style.marginLeft = '60px';
		}
	});

}( Joomla, document ));
