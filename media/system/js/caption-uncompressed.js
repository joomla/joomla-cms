/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JCaption javascript behavior
 *
 * Used for displaying image captions
 *
 * @package		Joomla
 * @since		1.5
 * @version		2.0
 */

var CAPTION = {}; 

(function($) {
	CAPTION.caption = function (selector) {
		$( selector ).each(function( index, element ) {
			var caption   = document.createTextNode(element.title);
			var container = document.createElement("div");
			var text      = document.createElement("p");
			var width     = element.getAttribute("width");
			var align     = element.getAttribute("align");
			
			if (!width) {
				width = element.width;
			}
		
			//Windows fix
			if (!align)
				align = element.getStyle("float");  // Rest of the world fix
			if (!align) // IE DOM Fix
				align = element.style.styleFloat;

			if (align=="" || !align) {
				align="none";
			}
			
			text.appendChild(caption);
			text.className = selector.replace('.', '_');

			element.parentNode.insertBefore(container, element);
			container.appendChild(element);
			if (element.title != "") {
				container.appendChild(text);
			}
			container.className   = selector.replace('.', '_');
			container.className   = container.className + " " + align;
			container.setAttribute("style","float:"+align);

			container.style.width = width + "px";
		});
	};
})(jQuery);