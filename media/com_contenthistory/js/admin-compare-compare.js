/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {
	document.addEventListener('DOMContentLoaded', function() {

		function compare(original, changed)
		{
			var display  = changed.nextElementSibling,
			    color    = '',
			    span     = null,
			    diff     = JsDiff.diffChars(original.innerHTML, changed.innerHTML),
			    fragment = document.createDocumentFragment();

			diff.forEach(function(part){
				color = part.added ? '#a6f3a6' : part.removed ? '#f8cbcb' : '';
				span = document.createElement('span');
				span.style.backgroundColor = color;
				span.style.borderRadius = '.2rem';
				span.appendChild(document.createTextNode(part.value));
				fragment.appendChild(span);
			});

			display.appendChild(fragment);
		}

		var diffs = document.querySelectorAll('.original');
		for (var i = 0, l = diffs.length; i < l; i++) {
			compare(diffs[i], diffs[i].nextElementSibling)
		}

	});
})();