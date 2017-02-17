/**
 * @copyright	Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

jQuery(document).ready(function() {
	var ajax_structure = {
		success: function(data, textStatus, jqXHR) {
			var link = jQuery('#plg_quickicon_extensionupdate').find('span.j-links-link');

			try {
				var updateInfoList = jQuery.parseJSON(data);
			} catch (e) {
				// An error occurred
				link.html(plg_quickicon_extensionupdate_text.ERROR);
			}

			if (updateInfoList instanceof Array) {
				if (updateInfoList.length == 0) {
					// No updates
					link.html(plg_quickicon_extensionupdate_text.UPTODATE);
				} else {
					var updateString = plg_quickicon_extensionupdate_text.UPDATEFOUND_MESSAGE.replace("%s", updateInfoList.length);
					jQuery('#system-message-container').prepend(
						'<div class="alert alert-error alert-joomlaupdate">'
						+ updateString
						+ ' <button class="btn btn-primary" onclick="document.location=\'' + plg_quickicon_extensionupdate_url + '\'">'
						+ plg_quickicon_extensionupdate_text.UPDATEFOUND_BUTTON + '</button>'
						+ '</div>'
					);
					var updateString = plg_quickicon_extensionupdate_text.UPDATEFOUND.replace("%s", updateInfoList.length);
					link.html(updateString);
				}
			} else {
				// An error occurred
				link.html(plg_quickicon_extensionupdate_text.ERROR);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// An error occurred
			jQuery('#plg_quickicon_extensionupdate').find('span.j-links-link').html(plg_quickicon_extensionupdate_text.ERROR);
		},
		url: plg_quickicon_extensionupdate_ajax_url + '&eid=0&skip=700'
	};
	ajax_object = new jQuery.ajax(ajax_structure);
});
