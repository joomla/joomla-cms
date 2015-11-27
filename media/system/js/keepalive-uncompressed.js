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
jQuery(window).on('load', function() {
	var jsonkeepalive = jQuery('[data-keepalive]').data('keepalive');
	window.setInterval(function() {
		var r;
		try
		{
			r = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch (e) {}
		if (r)
		{
			r.open('GET', jsonkeepalive.uri, true);
			r.send(null);
		}
	}, jsonkeepalive.interval);
});