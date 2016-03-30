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
				url: sendtestmail_url,
				data: email_data,
				dataFilter: function(data, type)
				{
					if ((typeof type === 'undefined' || type.indexOf('json') !== -1) 
						&& typeof data === 'string' && (data.indexOf('{') !== 0 || data[data.length-1] !== '}'))
					{
						var json = data.match(/{"success":(true|false),"message":.+,"messages":.+,"data":.+}/i);
						if (json)
						{
							if (typeof JSON.stringify === 'function')
							{
								var response = $.parseJSON(json[0]);
								if (typeof response.messages !== 'object') 
									response.messages = {};
								if (typeof response.messages.error === 'undefined') 
									response.messages.error = [];
								response.messages.error.push(data.replace(json[0], ' '));
								data = JSON.stringify(response);
							}
							else data = json[0];
						}
					}
					return data;
				}
			})

		.done(function (response)
		{
			var data_response = $.parseJSON(response);
			var msg = {};

			if (data_response.data)
			{
				if (typeof data_response.messages == 'object')
				{
					if (typeof data_response.messages.success != 'undefined' && data_response.messages.success.length > 0)
					{
						msg.success = [data_response.messages.success];
					}
				}

			}
			else
			{
				if (typeof data_response.messages == 'object')
				{
					if (typeof data_response.messages.error != 'undefined' && data_response.messages.error.length > 0)
					{
						msg.error = [data_response.messages.error];
					}

					if (typeof data_response.messages.notice != 'undefined' && data_response.messages.notice.length > 0)
					{
						msg.notice = [data_response.messages.notice];
					}

					if (typeof data_response.messages.message != 'undefined' && data_response.messages.message.length > 0)
					{
						msg.message = [data_response.messages.message];
					}
				}
			}

			Joomla.renderMessages(msg);
		});

		window.scrollTo(0, 0);
	});
});
