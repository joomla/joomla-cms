/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {

	if (!window.Joomla) {
		return;
	}

	var doTheCloak = function() {

		var options = Joomla.getOptions('email-cloak');

		for (var p in options) {
			if (typeof options[p] === 'object') {
				var el = document.getElementById('cloak-' + p);
				var newEl;
				var isSpan = false;

				if (el) {
					console.log(options[p].linkable, options[p].isEmail)
					if (options[p].linkable === true) {
						newEl = '<a ' + options[p].properties.before + ' href="mailto:' + options[p].properties.name + '@' + options[p].properties.domain +
							'" ' + options[p].properties.after + '>';
					} else {
						isSpan = true;
						newEl = '<span ' + options[p].properties.before + options[p].properties.after + '>';
					}

					if (options[p].isEmail === true) {
						newEl += options[p].properties.text !== '' ? options[p].properties.text : options[p].properties.name + '@' + options[p].properties.domain;
					}

					if (isSpan) {
						newEl += '</span>'
					} else {
						newEl += '</a>'
					}

					el.innerHTML = newEl;
				}
			}
		}
	};

	document.addEventListener('DOMContentLoaded', doTheCloak)

	// @todo: Add one more listener for Joomla:update eg: for ajax rendered content

})();