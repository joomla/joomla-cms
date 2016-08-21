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
 * @version  1.0
 */
!(function(){
	'use strict';
	// Keepalive function
	window.keepalive = function() {
		var keepalive_element  = document.getElementById('keepalive');
		if (keepalive_element)
		{
			var keepalive_uri = keepalive_element.getAttribute('data-keepalive-uri'), keepalive_interval = keepalive_element.getAttribute('data-keepalive-interval');
		}
		// If id attribute is not found in the script tag (script loaders extensions) set defaults
		else
		{
			var keepalive_uri = window.location.pathname + '?option=com_ajax&format=json', keepalive_interval = 0.75 * 60 * 1000;
		}
		setInterval(function() {
			var r;
			try
			{
				r = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) {}
			if (r)
			{
				r.open('GET', keepalive_uri, true);
				r.send(null);
			}
		}, keepalive_interval);
	};

	// If document already loaded when script is executed run keepalive
	if (document.readyState === 'complete' || document.readyState == "loaded")
	{
		window.keepalive();
	}
	// If document not yet loaded when script is executed run on onload event
	else
	{
		var listenerMethod = window.addEventListener || window.attachEvent, eventName = window.addEventListener ? 'load' : 'onload';
		listenerMethod(eventName, window.keepalive());
	}
})(window);
