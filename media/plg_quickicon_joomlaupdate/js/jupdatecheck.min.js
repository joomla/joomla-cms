/**
 * @copyright	Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * Ajax call to get the update status of Joomla
 */
(function() {
	"use strict";

	var checkForJoomlaUpdates = function() {
		if (Joomla.getOptions('js-joomla-update')) {
			var options = Joomla.getOptions('js-joomla-update');

			Joomla.request(
				{
					url: options.ajaxUrl + '&eid=700&cache_timeout=3600',
					method: 'GET',
					data:    '',
					perform: true,
					onSuccess: function(response, xhr)
					{
						var link     = document.getElementById('plg_quickicon_joomlaupdate'),
							linkSpan = link.querySelectorAll('span.j-links-link');

						var updateInfoList = JSON.parse(response);

						if (updateInfoList instanceof Array) {
							if (updateInfoList.length === 0) {
								/** No updates **/
								link.classList.add('success');
								for (var i = 0, len = linkSpan.length; i < len; i++) {
									linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_UPTODATE');
								}
							} else {
								var updateInfo = updateInfoList.shift();

								if (updateInfo.version != options.version) {
									var messages = {
										"message": [
											Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND_MESSAGE').replace("%s", updateInfoList.length)
											+ '<button class="btn btn-primary" onclick="document.location=\'' + options.url + '\'">'
											+ Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND_BUTOON') + '</button>'
										], "error": ["info"]
									};

									/** Render the message **/
									Joomla.renderMessages(messages);

									/** Scroll to page top **/
									window.scrollTo(0, 0);

									link.classList.add('danger');
									for (var i = 0, len = linkSpan.length; i < len; i++) {
										linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND').replace("%s", updateInfoList.length);
									}
								} else {
									for (var i = 0, len = linkSpan.length; i < len; i++) {
										linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_UPTODATE');
									}
								}
							}
						} else {
							/** An error occurred **/
							link.classList.add('danger');
							for (var i = 0, len = linkSpan.length; i < len; i++) {
								linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_ERROR');
							}
						}

					},
					onError: function(xhr)
					{
						/** An error occurred **/
						var link     = document.getElementById('plg_quickicon_joomlaupdate'),
							linkSpan = link.querySelectorAll('span.j-links-link');

						link.classList.add('danger');
						for (var i = 0, len = linkSpan.length; i < len; i++) {
							linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_ERROR');
						}
					}
				}
			);
		}
	};

	/** Add a listener on content loaded to initiate the check **/
	document.addEventListener('DOMContentLoaded', function() {
		setTimeout(checkForJoomlaUpdates, 2000)
	});
})();
