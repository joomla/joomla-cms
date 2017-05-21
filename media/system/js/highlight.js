/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Highlight javascript initiator
 *
 * @package     Joomla
 * @since       4.0
 * @version     1.0
 */
Joomla = window.Joomla || {};

(function(Joomla) {
	document.addEventListener('DOMContentLoaded',  function() {

		if (joomla.getOptions && typeof joomla.getOptions === 'function' && Joomla.getOptions('js-highlight')) {
			var options = Joomla.getOptions('js-highlight'),
                markOptions = {
	                exclude: [],
	                iframes: false,
	                iframesTimeout: 5000,
	                done: function(){},
	                debug: false,
                };

			if (options.selector) {
				var instance = new Mark(options.selector);
				instance.mark(options.highLight, markOptions);
			}
		}
	});
})(Joomla);
