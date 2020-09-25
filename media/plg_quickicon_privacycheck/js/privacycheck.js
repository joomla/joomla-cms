/**
 * @copyright	(C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

jQuery(document).ready(function() {
	var variables  = Joomla.getOptions('js-privacy-check'),
	    plg_quickicon_privacycheck_ajax_url = variables.plg_quickicon_privacycheck_ajax_url,
	    plg_quickicon_privacycheck_url = variables.plg_quickicon_privacycheck_url,
	    plg_quickicon_privacycheck_text = variables.plg_quickicon_privacycheck_text;
	var ajax_structure = {
		success: function(data, textStatus, jqXHR) {
			var link = jQuery('#plg_quickicon_privacycheck').find('span.j-links-link');

			try {
				var requestList = jQuery.parseJSON(data);
			} catch (e) {
				// An error occurred
				link.html(plg_quickicon_privacycheck_text.ERROR);
			}

			if (requestList.data.number_urgent_requests == 0) {
				// No requests
				link.html(plg_quickicon_privacycheck_text.NOREQUEST);
			} else {
				// Requests
				var msgString = '<span class="label label-important">'
					+ requestList.data.number_urgent_requests + '</span>&nbsp;'
					+ plg_quickicon_privacycheck_text.REQUESTFOUND_MESSAGE;

				jQuery('#system-message-container').prepend(
					'<div class="alert alert-error alert-joomlaupdate">'
					+ msgString
					+ ' <button class="btn btn-primary" onclick="document.location=\'' + plg_quickicon_privacycheck_url + '\'">'
					+ plg_quickicon_privacycheck_text.REQUESTFOUND_BUTTON + '</button>'
					+ '</div>'
				);

				var msgString = plg_quickicon_privacycheck_text.REQUESTFOUND
					+ '&nbsp;<span class="label label-important">'
					+ requestList.data.number_urgent_requests + '</span>'

				link.html(msgString);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// An error occurred
			jQuery('#plg_quickicon_privacycheck').find('span.j-links-link').html(plg_quickicon_privacycheck_text.ERROR);
		},
		url: plg_quickicon_privacycheck_ajax_url
	};
	ajax_object = new jQuery.ajax(ajax_structure);
});
