/**
 * @package     Joomla.Site
 * @subpackage  Templates.Cassiopeia
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

Joomla = window.Joomla || {};

(function(Joomla, document) {
	'use strict';

	document.addEventListener('DOMContentLoaded', function (event) {

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