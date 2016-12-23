/**
 * @copyright	Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
				link.html(Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_ERROR'));
			}

			if (updateInfoList instanceof Array) {
				var updateInfoListCount = updateInfoList.length;
				if (updateInfoListCount === 0) {
					// No updates
					link.html(Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_UPTODATE'));
				} else {
					var updateString = Joomla.JText.plural('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND_MESSAGE', updateInfoListCount);
					updateString = updateString.replace("%s", updateInfoListCount.toString());
					jQuery('#system-message-container').prepend(
						'<div class="alert alert-error alert-joomlaupdate">'
						+ updateString
						+ ' <button class="btn btn-primary" onclick="document.location=\'' + plg_quickicon_extensionupdate_url + '\'">'
						+ Joomla.JText.plural('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND_BUTTON', updateInfoListCount) + '</button>'
						+ '</div>'
					);
					var updateString = Joomla.JText.plural('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND', updateInfoListCount).replace("%s", updateInfoListCount.toString());
					link.html(updateString);
				}
			} else {
				// An error occurred
				link.html(Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_ERROR'));
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// An error occurred
			jQuery('#plg_quickicon_extensionupdate').find('span.j-links-link').html(Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_ERROR'));
		},
		url: plg_quickicon_extensionupdate_ajax_url + '&eid=0&skip=700'
	};
	ajax_object = new jQuery.ajax(ajax_structure);
});
