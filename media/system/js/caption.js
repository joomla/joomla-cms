/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JCaption javascript behavior
 *
 * Used for displaying image captions
 *
 * @package		Joomla
 * @since		1.5
 * @version     1.0
 */
(function() {
	var _createCaption = function(element, selector) {
		var caption   = document.createTextNode(element.title);
		var container = document.createElement("div");
		var text      = document.createElement("p");
		var width     = element.getAttribute("width");
		var align     = element.getAttribute("align");

		if(!width) {
			width = element.width;
		}

		text.appendChild(caption);
		element.parentNode.insertBefore(container, element);
		container.appendChild(element);
		if (element.title != "") {
			container.appendChild(text);
		}

		container.className = selector.replace('.', '_');
		if (align) {
			container.className = container.className+' '+align;
			container.setAttribute("style","float:"+align);
		}
		container.style.width = width + "px";
	};
	
	var JCaption = function(className) {
		var els = document.getElementsByTagName('img');
		var regexp = new RegExp('\\b'+className+'\\b', 'i');

		for (var i = 0, j = els.length; i < j; i++) {
			var el = els[i];
			if (regexp.test(el.className)) {
				_createCaption(el, className);
			}
		}
	};

	JCaption.create = function() {
		this.apply(this, arguments);
	};

	// Expose to global scope
	this.JCaption = JCaption;
})();

(function() {
	var tmp = window.onload || null;
	window.onload = function() {
		if (typeof tmp === 'function') {
			tmp();
		}
		JCaption.create('caption');
	}
})();