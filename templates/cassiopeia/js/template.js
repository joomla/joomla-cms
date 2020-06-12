/**
 * @package     Joomla.Site
 * @subpackage  Templates.Cassiopeia
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

Joomla = window.Joomla || {};

(function(Joomla, document) {
	'use strict';

	function initTemplate(event) {
		var target = event && event.target ? event.target : document;

		/**
		 * Prevent clicks on buttons within a disabled fieldset
		 */
		var fieldsets = target.querySelectorAll('fieldset.btn-group');
		for (var i = 0; i < fieldsets.length; i++) {
			var self = fieldsets[i];
			if (self.getAttribute('disabled') ===  true) {
				self.style.pointerEvents = 'none';
				var btns = self.querySelectorAll('.btn');
				for (var ib = 0; ib < btns.length; ib++) {
					btns[ib].classList.add('disabled');
				}
			}
		}
	}

	document.addEventListener('DOMContentLoaded', function (event) {
		initTemplate(event);

		/**
		 * Back to top
		 */
		var backToTop = document.getElementById('back-top');
		if (backToTop) {
			backToTop.addEventListener('click', function(event) {
				event.preventDefault();
				window.scrollTo(0, 0);
			});
		}
	});

	/**
	 * Initialize when a part of the page was updated
	 */
	document.addEventListener('joomla:updated', initTemplate);

})(Joomla, document);