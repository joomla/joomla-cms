/**
 * @copyright	Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JLinkManager behavior for link component
 *
 * @package		Joomla.Extensions
 * @subpackage	Content
 * @since		__DEPLOY_VERSION__
 */

(function ($)
{
	'use strict';

	window.LinkManager = {
		/**
		 * Saves the link attributes back to the parent window.
		 *
		 * @param   string  fieldid   The id of the field in the parent window.
		 * @param   string  oldvalue  The previous value of the field in the parent window.
		 *
		 * @return  void
		 */
		saveAttr: function(fieldid, oldvalue)
		{
			// Gather the values and build the json string from them.
			var f_url = document.getElementById('f_url').value,
				f_title = document.getElementById('f_title').value,
				f_rel = document.getElementById('f_rel').value,
				link = {url: f_url, title: f_title, rel: f_rel},
				linkjson = JSON.stringify(link),
				btn = window.parent.document.getElementById(fieldid + '-btn');
			// Update the iframe link such that the new data will be shown
			// if the the modal window is opened again.
			btn.href = btn.href.replace(oldvalue, 'link=' + linkjson);
			window.parent.jInsertFieldValue(linkjson, fieldid);
		},
		/**
		 * Closes the modal window.
		 *
		 * @return  void
		 */
		close: function()
		{
			window.parent.jQuery('.modal.in').modal('hide');
			window.parent.jModalClose();
		}
	};
}(jQuery));
