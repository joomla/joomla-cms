/**
 * @package         Joomla.JavaScript
 * @copyright       Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Calls the creation of a share URL
 */
function shareButton()
{
	// Remove js messages, if they exist.
	Joomla.removeMessages();

	jQuery.ajax({
		url: sharebuttonUrl,
		dataType: 'json'
	})
		.done(function (response) {
			// Check if the request was successful
			if (response.success !== true) {
				var messages = {
					"error": [response.message]
				};

				Joomla.renderMessages(messages);

				return false;
			}

			// Show the new share URL
			var div = document.getElementById('shareId');

			// Set the text
			div.innerHTML = '<span class="icon-share"></span>' + response.data;

			// Add the classes
			div.className = 'alert alert-success';

			// Render messages, if any.
			if (typeof response.messages === 'object' && response.messages !== null) {
				Joomla.renderMessages(response.messages);

				window.scrollTo(0, 0);

			}
		})
		.fail(function(xhr, ajaxOptions, thrownError) {
			var messages = {
				"error": [thrownError]
			};

			Joomla.renderMessages(messages);

			window.scrollTo(0, 0);
		});

}