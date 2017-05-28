/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function( Joomla, document ) {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		if (Joomla.getOptions('modal-associations')) {
			var itemId = Joomla.getOptions('modal-associations').itemId;

			// @TODO function should not be global, move it to Joomla
			window['jSelectAssociation_' + itemId] = function(id) {
				target = document.getElementById('target-association');
				document.getElementById('target-association').src = target.getAttribute('data-editurl') +
					'&task=' + target.getAttribute('data-item') + '.edit' + '&id=' + id;
				jQuery('#associationSelect' + itemId + 'Modal').modal('hide');
			}
		}
	});

})();
