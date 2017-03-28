/**
 * @package         Joomla.JavaScript
 * @copyright       Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Calls the sending process of the config class
 */
var sendTestMail = function ()
{
	$ = jQuery;

	var email_data = {
		smtpauth  : $('input[name="jform[smtpauth]"]:checked').val(),
		smtpuser  : $('input[name="jform[smtpuser]"]').val(),
		smtppass  : $('input[name="jform[smtppass]"]').val(),
		smtphost  : $('input[name="jform[smtphost]"]').val(),
		smtpsecure: $('select[name="jform[smtpsecure]"]').val(),
		smtpport  : $('input[name="jform[smtpport]"]').val(),
		mailfrom  : $('input[name="jform[mailfrom]"]').val(),
		fromname  : $('input[name="jform[fromname]"]').val(),
		mailer    : $('select[name="jform[mailer]"]').val(),
		mailonline: $('input[name="jform[mailonline]"]:checked').val()
	};

	// Remove js messages, if they exist.
	Joomla.removeMessages();

	$.ajax({
		method: "POST",
		url: document.getElementById('sendtestmail').getAttribute('data-ajaxuri'),
		data: email_data,
		dataType: "json"
	})
	.fail(function (jqXHR, textStatus, error) {
		Joomla.renderMessages(Joomla.ajaxErrorsMessages(jqXHR, textStatus, error));

		window.scrollTo(0, 0);
	})
	.done(function (response) {
		// Render messages, if any.
		if (typeof response.messages == 'object' && response.messages !== null)
		{
			Joomla.renderMessages(response.messages);

			window.scrollTo(0, 0);
		}
	});
};

jQuery(document).ready(function ($)
{
	$('#sendtestmail').click(sendTestMail);
});
