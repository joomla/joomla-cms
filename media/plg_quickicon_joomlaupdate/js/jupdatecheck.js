/**
 * @copyright	Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

var plg_quickicon_jupdatecheck_ajax_structure = {};

jQuery(document).ready(function() {
	plg_quickicon_jupdatecheck_ajax_structure = {
		success: function(data, textStatus, jqXHR) {
			var link = jQuery('#plg_quickicon_joomlaupdate').find('span.j-links-link');

			try {
				var updateInfoList = jQuery.parseJSON(data);
			} catch (e) {
				// An error occurred
				link.html(plg_quickicon_joomlaupdate_text.ERROR);
			}

			if (updateInfoList instanceof Array) {
				if (updateInfoList.length < 1) {
					// No updates
					link.replaceWith(plg_quickicon_joomlaupdate_text.UPTODATE);
				} else {
					var updateInfo = updateInfoList.shift();
					if (updateInfo.version != plg_quickicon_jupdatecheck_jversion) {
						var updateString = plg_quickicon_joomlaupdate_text.UPDATEFOUND.replace("%s", updateInfo.version + "");
						jQuery('#plg_quickicon_joomlaupdate').find('.j-links-link').html(updateString);
						var updateString = plg_quickicon_joomlaupdate_text.UPDATEFOUND_MESSAGE.replace("%s", updateInfo.version + "");
						jQuery('#system-message-container').prepend(
							'<div class="alert alert-error alert-joomlaupdate">'
							+ updateString
							+ ' <button class="btn btn-primary" onclick="document.location=\'' + plg_quickicon_joomlaupdate_url + '\'">'
							+ plg_quickicon_joomlaupdate_text.UPDATEFOUND_BUTTON + '</button>'
							+ '</div>'
						);
					} else {
						link.html(plg_quickicon_joomlaupdate_text.UPTODATE);
					}
				}
			} else {
				// An error occurred
				link.html(plg_quickicon_joomlaupdate_text.ERROR);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// An error occurred
			jQuery('#plg_quickicon_joomlaupdate').find('span.j-links-link').html(plg_quickicon_joomlaupdate_text.ERROR);
		},
		url: plg_quickicon_joomlaupdate_ajax_url + '&eid=700&cache_timeout=3600'
	};
	setTimeout("ajax_object = new jQuery.ajax(plg_quickicon_jupdatecheck_ajax_structure);", 2000);
});
