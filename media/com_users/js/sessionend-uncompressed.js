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
	$('#end_session').click(function ()
	{
		$.ajax({
			method: "POST",
			url: endsession_url,
			data: {'user_id': user_id},
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

			if (response.data)
			{
				if (response.data.hasOwnProperty('success'))
				{
					msg.success = [response.data.success];
				}
				else if (response.data.hasOwnProperty('error'))
				{
					msg.error = [response.data.error];
				}
			}

			Joomla.renderMessages(msg);
		});

		window.scrollTo(0, 0);
	});
});
