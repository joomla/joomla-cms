/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function( Joomla, window) {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		if (Joomla.getOptions('modal-associations')) {
			var itemId = Joomla.getOptions('modal-associations').itemId;

			// @TODO function should not be global, move it to Joomla
			window['jSelectAssociation_' + itemId] = function(id) {
				var target = document.getElementById('target-association');

				if (target)
				{
					target.src = target.getAttribute('data-editurl') +
						'&task=' + target.getAttribute('data-item') + '.edit' + '&id=' + id;
				}

				Joomla.Modal.getCurrent().close();
			}
		}
	});

})(Joomla, window);
