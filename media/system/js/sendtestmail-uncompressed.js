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
				// Html entity encode.
				var encodedJson = jqXHR.responseText.trim();

				var buf = [];
				for (var i = encodedJson.length-1; i >= 0; i--) {
					buf.unshift( [ '&#', encodedJson[i].charCodeAt(), ';' ].join('') );
				}

				encodedJson = buf.join('');

				msg.error = [ Joomla.JText._('COM_CONFIG_SENDMAIL_JS_ERROR_PARSE').replace('%s', encodedJson) ];
			}
			else if (textStatus == 'nocontent')
			{
				msg.error = [ Joomla.JText._('COM_CONFIG_SENDMAIL_JS_ERROR_NO_CONTENT') ];
			}
			else if (textStatus == 'timeout')
			{
				msg.error = [ Joomla.JText._('COM_CONFIG_SENDMAIL_JS_ERROR_TIMEOUT') ];
			}
			else if (textStatus == 'abort')
			{
				msg.error = [ Joomla.JText._('COM_CONFIG_SENDMAIL_JS_ERROR_CONNECTION_ABORT') ];
			}
			else
			{
				msg.error = [ Joomla.JText._('COM_CONFIG_SENDMAIL_JS_ERROR_OTHER').replace('%s', jqXHR.status) ];
			}

			Joomla.renderMessages(msg);
		})
		.done(function (response) {
			var msg = {};

			if (typeof response.messages == 'object')
			{
				if (response.data && typeof response.messages.success != 'undefined' && response.messages.success.length > 0)
				{
					msg.success = [response.messages.success];
				}

				if (typeof response.messages.error != 'undefined' && response.messages.error.length > 0)
				{
					msg.error = [response.messages.error];
				}

				if (typeof response.messages.warning != 'undefined' && response.messages.warning.length > 0)
				{
					msg.warning = [response.messages.warning];
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

			Joomla.renderMessages(msg);
		});

		window.scrollTo(0, 0);
	});
});
