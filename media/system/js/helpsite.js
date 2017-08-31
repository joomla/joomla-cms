/**
 * @package		Joomla.JavaScript
 * @copyright	Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Rebuilds the list with the available help sites
 */
"use strict";

var resetHelpSiteList = function() {

	// Uses global variable helpsite_base for bast uri
	var select_id   = this.getAttribute('rel');
	var showDefault = this.getAttribute('showDefault');

	Joomla.request(
		{
			url:    'index.php?option=com_users&task=profile.gethelpsites&format=json',
			method: 'GET',
			data:    '',
			perform: true,
			headers: {'Content-Type': 'application/json;charset=utf-8'},
			onSuccess: function(response, xhr)
			{
				response = JSON.parse(response);

				// The response contains the options to use in help site select field
				var node;

				document.getElementById(select_id).innerHTML = '';

				// Build options
				response.forEach(function(item) {
					if (item.value !== '' || showDefault === 'true') {
						node = document.createElement('option');
						node.value = item.value;
						node.innerHTML = item.text;
						document.getElementById(select_id).appendChild(node);
					}
				});
			},
			onError: function(xhr)
			{
				// Remove js messages, if they exist.
				Joomla.removeMessages();

				Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
			}
		}
	);
};

document.addEventListener('DOMContentLoaded', function() {
	document.getElementById('helpsite-refresh').addEventListener('click', resetHelpSiteList);
});
