/**
 * @package         Joomla.JavaScript
 * @copyright       Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Calls the sending process of the config class
 */
jQuery(document).ready(function ($)
{
	$('#sendtestmail').click(function ()
	{
		var email_data = {
			smtpauth  : $('input[name="jform[smtpauth]"]').val(),
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

		$.ajax({
			method: "POST",
			url: sendtestmail_url,
			data: email_data,
			dataType: "json"
		})
		.fail(function (jqXHR, textStatus, error) {
			var msg = {};
			if (textStatus == 'parsererror')
			{
				msg.error = ['A parse error as occured while processing the following JSON data:<br/><code style="color:inherit;white-space:pre;padding:0;margin:0;border:0;background:inherit;">' + jqXHR.responseText.trim() + '</code>'];
			}
			else if (textStatus == 'nocontent')
			{
				msg.error = ['No content has returned.'];
			}
			else if (textStatus == 'timeout')
			{
				msg.error = ['A timeout as occured while fetching the JSON data.'];
			}
			else if (textStatus == 'abort')
			{
				msg.error = ['A connection abort as occured while fetching the JSON data.'];
			}
			else
			{
				msg.error = ['An error as occured while fetching the JSON data: ' + jqXHR.status + ' HTTP status code.'];
			}
			Joomla.renderMessages(msg);
		})
		.done(function (response) {
			var msg = {};

			if (response.data)
			{
				if (typeof response.messages == 'object')
				{
					if (typeof response.messages.success != 'undefined' && response.messages.success.length > 0)
					{
						msg.success = [response.messages.success];
					}
				}
			}
			else
			{
				if (typeof response.messages == 'object')
				{
					if (typeof response.messages.error != 'undefined' && response.messages.error.length > 0)
					{
						msg.error = [response.messages.error];
					}

					if (typeof response.messages.notice != 'undefined' && response.messages.notice.length > 0)
					{
						msg.notice = [response.messages.notice];
					}

					if (typeof response.messages.message != 'undefined' && response.messages.message.length > 0)
					{
						msg.message = [response.messages.message];
					}
				}
			}

			Joomla.renderMessages(msg);
		});

		window.scrollTo(0, 0);
	});
});
