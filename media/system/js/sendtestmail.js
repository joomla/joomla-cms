/**
 * @package         Joomla.JavaScript
 * @copyright       Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Calls the sending process of the config class
 */
"use strict";

var sendTestMail = function() {

	var email_data = {
		smtpauth  : document.querySelector('[name="jform[smtpauth]"]').value,
		smtpuser  : document.querySelector('[name="jform[smtpuser]"]').value,
		smtppass  : document.querySelector('[name="jform[smtppass]"]').value,
		smtphost  : document.querySelector('[name="jform[smtphost]"]').value,
		smtpsecure: document.querySelector('[name="jform[smtpsecure]"]').value,
		smtpport  : document.querySelector('[name="jform[smtpport]"]').value,
		mailfrom  : document.querySelector('[name="jform[mailfrom]"]').value,
		fromname  : document.querySelector('[name="jform[fromname]"]').value,
		mailer    : document.querySelector('[name="jform[mailer]"]').value,
		mailonline: document.querySelector('[name="jform[mailonline]"]').value
	};

	// Remove js messages, if they exist.
	Joomla.removeMessages();

	Joomla.request(
		{
			url:    document.getElementById('sendtestmail').getAttribute('data-ajaxuri'),
			method: 'POST',
			data:    JSON.stringify(email_data),
			perform: true,
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			onSuccess: function(response, xhr)
			{
				response = JSON.parse(response);
				if (typeof response.messages == 'object' && response.messages !== null) {
					Joomla.renderMessages(response.messages);
				}
			},
			onError: function(xhr)
			{
				Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
			}
		}
	);
};

document.addEventListener('DOMContentLoaded', function() {
	document.getElementById('sendtestmail').addEventListener('click', sendTestMail);
});
