/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JMenu javascript behavior
 *
 * @package		Joomla
 * @since		1.5
 * @version     1.0
 */
var JMenu = new Class({
	initialize: function(el)
	{
		var elements = document.id(el).getElements('li');
		var nested = null
		for (var i=0; i<elements.length; i++)
		{
			var element = elements[i];

			element.addEvent('mouseover', function(){ this.addClass('hover'); });
			element.addEvent('mouseout', function(){ this.removeClass('hover'); });

			//find nested UL
			nested = element.getElement('ul');
			if(!nested) {
				continue;
			}

			//declare width
			var offsetWidth  = 0;

			//find longest child
			for (k=0; k < nested.childNodes.length; k++) {
				var node  = nested.childNodes[k]
				if (node.nodeName == "LI")
					offsetWidth = (offsetWidth >= node.offsetWidth) ? offsetWidth :  node.offsetWidth;
			}

			//match longest child
			for (l=0; l < nested.childNodes.length; l++) {
				var node = nested.childNodes[l]
				if (node.nodeName == "LI") {
					document.id(node).setStyle('width', offsetWidth+'px');
				}
			}

			document.id(nested).setStyle('width', offsetWidth+'px');
		}
	}
});

document.menu = null;
window.addEvent('load', function(){
	element = document.id('menu');
	if(!element.hasClass('disabled')) {
		var menu = new JMenu(element);
		document.menu = menu;
	}
});