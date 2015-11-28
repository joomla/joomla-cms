/**
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Keepalive javascript behavior
 *
 * Used for keeping the session alive
 *
 * @package     Joomla
 * @since       3.5
 * @version  1.0
 */
// Keepalive function
window.keepalive = function() {
	var keepalive_element  = document.getElementById('keepalive');
	var keepalive_uri = keepalive_element.getAttribute('data-keepalive-uri'), keepalive_interval = keepalive_element.getAttribute('data-keepalive-interval');
	window.setInterval(function() {
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
	keepalive();
}
// If document not yet loaded when script is executed run on onload event
else
{
	var type = 'load', target = window;
	var listenerMethod = target.addEventListener || target.attachEvent, eventName = target.addEventListener ? type : 'on' + type;
    listenerMethod(type, keepalive());
}