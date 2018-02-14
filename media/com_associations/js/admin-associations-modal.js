/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function( Joomla, document ) {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		
		if (Joomla.getOptions('modal-associations')) {
			var fnName = Joomla.getOptions('modal-associations').func,
				links  = [].slice.call(document.querySelectorAll('.select-link'));

			links.forEach(function (item) {
				item.addEventListener('click', function (event) {
					if (self != top) {
						// Run function on parent window.
						window.parent[fnName](event.target.getAttribute('data-id'));
					}
				});
			});
		}
	});
})();
