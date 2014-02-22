/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

jQuery(document).ready(function() {
	var ajax_structure = {
		success: function(data, textStatus, jqXHR) {
			try {
				var updateInfoList = jQuery.parseJSON(data);
			} catch(e) {
				// An error occured
				jQuery('#plg_quickicon_extensionupdate').find('span').html(plg_quickicon_extensionupdate_text.ERROR);
			}
			if (updateInfoList instanceof Array) {
				if (updateInfoList.length < 1) {
					// No updates
					jQuery('#plg_quickicon_extensionupdate').find('span').html(plg_quickicon_extensionupdate_text.UPTODATE);
				} else {
					var updateString = plg_quickicon_extensionupdate_text.UPDATEFOUND.replace("%s", updateInfoList.length);
					jQuery('#plg_quickicon_extensionupdate').find('span').html(updateString);
				}
			} else {
				// An error occured
				jQuery('#plg_quickicon_extensionupdate').find('span').html(plg_quickicon_extensionupdate_text.ERROR);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// An error occured
			jQuery('#plg_quickicon_extensionupdate').find('span').html(plg_quickicon_extensionupdate_text.ERROR);
		},
		url: plg_quickicon_extensionupdate_ajax_url + '&eid=0&skip=700'
	};
	ajax_object = new jQuery.ajax(ajax_structure);
});
