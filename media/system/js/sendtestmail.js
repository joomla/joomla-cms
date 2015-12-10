/**
 * @package         Joomla.JavaScript
 * @copyright       Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Calls the sending process of the config class
 */
jQuery(document).ready(function ($)
{
	$('#sendtestmail').click(function ()
	{
		var data = {
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
			url: sendtestmail_url,
			data: data
		})

		.done(function (response)
		{
			var data = $.parseJSON(response);
			var msg = {};

			if (data.data)
			{
				msg.success = [success_sendmail];
			}
			else
			{
				msg.error = [error_sendmail];

				if (typeof data.messages == 'object')
				{
					if (typeof data.messages.error != 'undefined' && data.messages.error.length > 0)
					{
						msg.error.push(data.messages.error);
					}

					if (typeof data.messages.notice != 'undefined' && data.messages.notice.length > 0)
					{
						msg.notice = [data.messages.notice]
					}

					if (typeof data.messages.message != 'undefined' && data.messages.message.length > 0)
					{
						msg.message = [data.messages.message];
					}
				}
			}

			Joomla.renderMessages(msg);
		});

		window.scrollTo(0, 0);
	});
});
