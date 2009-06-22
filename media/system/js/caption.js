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
var JCaption = (function(selectorFn) {
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
	
	var JCaption = function(selector, fn) {
		if (typeof fn === 'function') {
			selectorFn = fn;
		}
		selectorFn(selector).each(function(el) {
			_createCaption(el, selector);
		});
	};
	JCaption.create = function() {
		this.apply(this, arguments);
	};
	return JCaption;
})($$);

window.addEvent('load', function() {
	JCaption.create('img.caption');
});
