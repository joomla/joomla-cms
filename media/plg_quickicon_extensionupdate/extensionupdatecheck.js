/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

window.addEvent('domready', function(){
	var ajax_structure = {
		onSuccess: function(msg, responseXML)
		{
			try {
				var updateInfoList = JSON.decode(msg, true);
			} catch(e) {
				// An error occured
				document.id('plg_quickicon_extensionupdate').getElements('span').set('html', plg_quickicon_extensionupdate_text.ERROR);
			}
			if (updateInfoList instanceof Array) {
				if (updateInfoList.length < 1) {
					// No updates
					document.id('plg_quickicon_extensionupdate').getElements('span').set('html', plg_quickicon_extensionupdate_text.UPTODATE);
				} else {
					var updateString = plg_quickicon_extensionupdate_text.UPDATEFOUND.replace("%s", updateInfoList.length);
					document.id('plg_quickicon_extensionupdate').getElements('span').set('html', updateString);
				}
			} else {
				// An error occured
				document.id('plg_quickicon_extensionupdate').getElements('span').set('html', plg_quickicon_extensionupdate_text.ERROR);
			}
		},
		onFailure: function(req) {
			// An error occured
			document.id('plg_quickicon_extensionupdate').getElements('span').set('html', plg_quickicon_extensionupdate_text.ERROR);
		},
		url: plg_quickicon_extensionupdate_ajax_url
	};
	ajax_object = new Request(ajax_structure);
	ajax_object.send('eid=0&skip=700');
});