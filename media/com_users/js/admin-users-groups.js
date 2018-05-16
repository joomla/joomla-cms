/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function(Joomla) {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {

		Joomla.submitbutton = function(task) {
			if (task === 'groups.delete') {
				var i, cids = document.getElementsByName('cid[]');
				for (i = 0; i < cids.length; i++) {
					if (cids[i].checked && cids[i].parentNode.getAttribute('data-usercount') != 0) {
						if (confirm(Joomla.JText._('COM_USERS_GROUPS_CONFIRM_DELETE'))) {
							Joomla.submitform(task);
						}
						return false;
					}
				}
			}

			Joomla.submitform(task);
			return false;
		};

	});

})(Joomla);
