/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Keepalive javascript behavior
 *
 * Used for keeping the session alive
 *
 * @package  Joomla
 * @since    3.7.0
 */
!(function(){
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {

		var keepaliveOptions  = Joomla.getOptions('system.keepalive'),
		    keepaliveUri      = keepaliveOptions && keepaliveOptions.uri ? keepaliveOptions.uri.replace(/&amp;/g, '&') : '',
		    keepaliveInterval = keepaliveOptions && keepaliveOptions.interval ? keepaliveOptions.interval : 45 * 1000;

		// Fallback in case no keepalive uri was found.
		if (keepaliveUri === '')
		{
			var systemPaths = Joomla.getOptions('system.paths');

			keepaliveUri = (systemPaths ? systemPaths.root + '/index.php' : window.location.pathname) + '?option=com_ajax&format=json';
		}

		window.setInterval(function() {
			Joomla.request({
				url: keepaliveUri,
				onSuccess: function(response, xhr)
				{
					// Do nothing
				},
				onError: function(xhr)
				{
					// Do nothing
				}
			});
		}, keepaliveInterval);

	});

})(window, document, Joomla);
