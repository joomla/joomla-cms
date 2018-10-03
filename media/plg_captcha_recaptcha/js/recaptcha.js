/**
 * @package		Joomla.JavaScript
 * @copyright	Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
window.JoomlaInitReCaptcha2 = function() {
	'use strict';

	var items = document.getElementsByClassName('g-recaptcha'),
	    item,
	    option_keys = ['sitekey', 'theme', 'size', 'tabindex', 'callback', 'expired-callback', 'error-callback'],
	    options = {},
	    option_key_fq
	;

	for (var i = 0, l = items.length; i < l; i++) {
		item = items[i];
		if (item.dataset) {
			options = item.dataset;
		} else {
			for (var j = 0; j < option_keys.length; j++) {
				option_key_fq = ('data-' + option_keys[j]);
				if (item.hasAttribute(option_key_fq)) {
					options[option_keys[j]] = item.getAttribute(option_key_fq);
				}
			}
		}

		// Set the widget id of the recaptcha item
		item.setAttribute(
			'data-recaptcha-widget-id',
			grecaptcha.render(item, options)
		);
	}
};
