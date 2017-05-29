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
		if (Joomla.getOptions && typeof Joomla.getOptions === 'function' && Joomla.getOptions('js-highlight')) {
			var options = Joomla.getOptions('js-highlight'),
			    markOptions = {
				    "exclude": [],
				    "separateWordSearch": true,
				    "accuracy": "partially",
				    "diacritics": true,
				    "synonyms": {},
				    "iframes": false,
				    "iframesTimeout": 5000,
				    "acrossElements": true,
				    "caseSensitive": false,
				    "ignoreJoiners": false,
				    "wildcards": "disabled",
			    };

			// Continue only if the element exists
			if (options.class) {
				var element = document.querySelector("." + options.class);

				if (element) {
					var instance = new Mark(element);

					// Loop through the terms
					options.highLight.forEach(function(term) {
						instance.mark(term, markOptions);
					})
				}
			} else if (options.compatibility) {
				var start = document.querySelector(options.start),
				    end = document.querySelector(options.end);

				var parent = start.parentNode,
				    targetNodes = [],
				    redundant = true;
				    allElems = parent.childNodes;

				// Remove all elements till start element
				for (var i = 0, l = allElems; i < l; i++) {
					if (allElems[i] !== start && redundant === true) {
						allElems[i].parentNode.removeChild(allElems[i]);
					} else if (allElems[i] === start) {
						redundant = false;
					} else {
						targetNodes.push(allElems[i])
					}
				}

				// Remove all elements after end element
				for (var i = 0, l = targetNodes; i < l; i++) {
					if (targetNodes[i] === end) {
						redundant = true;
					} else if (redundant === true) {
						targetNodes[i].parentNode.removeChild(targetNodes[i]);
					}
				}

				for (var i = 0, l = targetNodes; i < l; i++) {
					var instance = new Mark(targetNodes[i]);
					// Loop through the terms
					options.highLight.forEach(function(term) {
						instance.mark(term, markOptions);
					})
				}
			}
		}
	});
})(Joomla);
