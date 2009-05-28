/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
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
var JCaption = new Class({
	initialize: function(selector)
	{
		this.selector = selector;
		var images = $$(selector);
		images.each(function(image){ this.createCaption(image); }, this);
	},

	createCaption: function(element)
	{
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
		if ( element.title != "" ) {
			container.appendChild(text);
		}
		container.className   = this.selector.replace('.', '_');
		container.className   = container.className + " " + align;
		container.setAttribute("style","float:"+align);
		container.style.width = width + "px";

	}
});

window.addEvent('load', function() {
  var caption = new JCaption('img.caption')
});
