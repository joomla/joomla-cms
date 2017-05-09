/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(function(window, Joomla) {
	'use strict';

	document.addEventListener('DOMContentLoaded', function(){

		if (Joomla.getOptions('js-category-edit')) {
			var options = Joomla.getOptions('js-category-edit');
console.log(options['search'])
			if (options['elementId']) {
				new Choices(document.getElementById(options['elementId']), {
					addItems: options['addItems'] ? options['addItems'] : false,
					duplicateItems: options['duplicateItems'] ? options['duplicateItems'] : false,
					flip: options['flip'] ? options['flip'] : false,
					shouldSort: options['shouldSort'] ? options['shouldSort'] : false,
					search: options['search'] ? options['search'] : false,
				});
			}
		}
	});
})(window, Joomla);

