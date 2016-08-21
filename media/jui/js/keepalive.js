/**
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Keepalive javascript behavior
 *
 * Used for keeping the session alive
 *
 * @package  Joomla
 * @since    __DEPLOY_VERSION__
 */
!(function(){
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {

		var keepaliveElement  = document.getElementById('keepalive'),
		    keepaliveUri      = keepaliveElement ? keepaliveElement.getAttribute('data-uri') : window.location.pathname + '?option=com_ajax&format=json',
		    keepaliveInterval = keepaliveElement ? keepaliveElement.getAttribute('data-interval') : 0.75 * 60 * 1000;

		window.setInterval(function() {
			var r;
			try
			{
				r = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) {}
			if (r)
			{
				r.open('GET', keepaliveUri, true);
				r.send(null);
			}
		}, keepaliveInterval);

	});

})(window);
